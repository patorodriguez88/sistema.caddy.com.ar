import tempfile
import unittest
from pathlib import Path
from repair_sandbox_ftp_state import repaired_state, definitely_missing


class ManifestRepairTest(unittest.TestCase):
    def test_only_nonexistent_directories_are_forgotten(self):
        state = {'version': '1', 'data': [
            {'type': 'folder', 'name': 'current'},
            {'type': 'file', 'name': 'current/index.php', 'hash': 'keep'},
            {'type': 'folder', 'name': 'obsolete'},
            {'type': 'file', 'name': 'obsolete/old.js'},
            {'type': 'folder', 'name': 'obsolete/nested'},
            {'type': 'folder', 'name': 'still-remote'},
            {'type': 'file', 'name': 'orphan-file.php'},
        ]}
        with tempfile.TemporaryDirectory() as root:
            (Path(root) / 'current').mkdir()
            checked = []
            def exists(name):
                checked.append(name)
                return name == 'still-remote'
            result, count = repaired_state(state, Path(root), exists)
        self.assertEqual(count, 3)
        self.assertEqual(checked, ['obsolete', 'still-remote'])
        self.assertEqual(result['version'], '1')
        self.assertEqual(len(state['data']), 7)  # original untouched
        self.assertEqual([e['name'] for e in result['data']],
                         ['current', 'current/index.php', 'still-remote', 'orphan-file.php'])
        self.assertEqual(result['data'][1]['hash'], 'keep')

    def test_permission_and_network_errors_never_rewrite_state(self):
        self.assertFalse(definitely_missing('550 Permission denied'))
        self.assertFalse(definitely_missing('530 Login incorrect'))
        self.assertTrue(definitely_missing("550 Can't change directory: No such file or directory"))
        state = {'data': [{'type': 'folder', 'name': 'old'}]}
        def denied(name):
            raise PermissionError('Permission denied')
        with self.assertRaises(PermissionError):
            repaired_state(state, Path('/nonexistent-local-checkout'), denied)
        self.assertEqual(len(state['data']), 1)

    def test_unsafe_paths_are_rejected(self):
        for name in ('../escape', '/absolute'):
            with self.assertRaises(ValueError):
                repaired_state({'data': [{'type': 'folder', 'name': name}]}, Path('/tmp'), lambda p: False)


if __name__ == '__main__':
    unittest.main()
