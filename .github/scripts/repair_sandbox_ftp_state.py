"""Repair stale FTP directory entries; never delete application files or reset state.

FTP-Deploy tolerates missing files, but aborts while removing a missing directory.
Only forget directories absent from BOTH this checkout and the remote FTP root.
Permission errors and unknown responses fail closed without rewriting the manifest.
"""
import io
import json
import os
from ftplib import FTP, error_perm
from pathlib import Path, PurePosixPath

STATE_NAME = '.ftp-deploy-sync-state.json'


def definitely_missing(error):
    message = str(error).lower()
    return message.startswith('550') and 'no such file or directory' in message


def repaired_state(state, local_root, remote_directory_exists):
    if not isinstance(state, dict) or not isinstance(state.get('data'), list):
        raise ValueError('Unexpected FTP manifest format; refusing to modify it')
    absent = []
    folders = sorted((e for e in state['data'] if e.get('type') == 'folder'),
                     key=lambda e: (e.get('name', '').count('/'), e.get('name', '')))
    for entry in folders:
        name = entry.get('name', '').rstrip('/')
        path = PurePosixPath(name)
        if not name or path.is_absolute() or '..' in path.parts:
            raise ValueError('Unsafe directory entry in FTP manifest')
        if any(name == p or name.startswith(p + '/') for p in absent):
            continue
        if (local_root / name).exists():
            continue
        if not remote_directory_exists(name):
            absent.append(name)
    result = dict(state)
    result['data'] = [e for e in state['data'] if not any(
        e.get('name', '').rstrip('/') == p or e.get('name', '').startswith(p + '/')
        for p in absent)]
    return result, len(state['data']) - len(result['data'])


def main():
    with FTP(os.environ['FTP_SERVER'], timeout=30) as ftp:
        ftp.login(os.environ['FTP_USER'], os.environ['FTP_PASSWORD'])
        remote_root = ftp.pwd()
        buffer = io.BytesIO()
        try:
            ftp.retrbinary('RETR ' + STATE_NAME, buffer.write)
        except error_perm as error:
            if definitely_missing(error):
                print('No existing deployment manifest; nothing to repair.')
                return
            raise
        state = json.loads(buffer.getvalue().decode('utf-8'))

        def directory_exists(name):
            ftp.cwd(remote_root)
            try:
                ftp.cwd(name)
                return True
            except error_perm as error:
                if definitely_missing(error):
                    return False
                raise
            finally:
                ftp.cwd(remote_root)

        repaired, count = repaired_state(state, Path.cwd(), directory_exists)
        if count:
            payload = json.dumps(repaired, ensure_ascii=False, indent=4).encode('utf-8')
            ftp.storbinary('STOR ' + STATE_NAME, io.BytesIO(payload))
        print(f'FTP manifest checked: {count} nonexistent entries removed. Application files unchanged.')


if __name__ == '__main__':
    main()
