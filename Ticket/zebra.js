
public interface ZebraPrinter
extends FileUtil, GraphicsUtil, FormatUtil, ToolsUtil
An interface used to obtain various properties of a Zebra printer.

package test.zebra.sdk.printer.examples;
 
 import com.zebra.sdk.comm.Connection;
 import com.zebra.sdk.comm.ConnectionException;
 import com.zebra.sdk.comm.TcpConnection;
 import com.zebra.sdk.printer.PrinterLanguage;
 import com.zebra.sdk.printer.ZebraPrinter;
 import com.zebra.sdk.printer.ZebraPrinterFactory;
 import com.zebra.sdk.printer.ZebraPrinterLanguageUnknownException;
 
 public class ZebraPrinterExample {
 
     public static void main(String[] args) throws Exception {
         Connection connection = new TcpConnection("10.0.1.18", TcpConnection.DEFAULT_ZPL_TCP_PORT);
         try {
             connection.open();
             ZebraPrinter zPrinter = ZebraPrinterFactory.getInstance(connection);
             PrinterLanguage pcLanguage = zPrinter.getPrinterControlLanguage();
             System.out.println("Printer Control Language is " + pcLanguage);
             connection.close();
         } catch (ConnectionException e) {
             e.printStackTrace();
         } catch (ZebraPrinterLanguageUnknownException e) {
             e.printStackTrace();
         } finally {
             connection.close();
         }
     }
 }
