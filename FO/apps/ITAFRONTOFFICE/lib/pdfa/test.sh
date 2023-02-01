echo
echo "Command-line samples for PDF/A Manager."
echo "Copyright 2003-2012 PDFTron Systems Inc."
echo

echo "Example 1) Test a PDF file for PDF/A compliance:" 
./pdfa --noxml license.pdf
echo "Example 2) Test PDF files for PDF/A compliance and generate an XML error report:" 
./pdfa -o OUT1 --level B --subfolders *.pdf Folder2
echo "Example 3) An example of PDF/A Conversion:" 
./pdfa -c -z -o OUT2 *.pdf
echo "Example 4) An example of PDF/A Conversion:" 
./pdfa -c --noxml -o OUT3 --subfolders license.pdf OUT2

