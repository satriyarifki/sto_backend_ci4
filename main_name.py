import glob
import pytesseract
from pdf2image import convert_from_path
import json
import sys

# Read PFIC name faktur using OCR Python

def extract_nama_faktur_from_pdf(pdf_path, name_fakturs):
    images = convert_from_path(pdf_path, use_pdftocairo=True)
    last_page_image = images[-1]
    text = pytesseract.image_to_string(last_page_image, lang='eng')
    for name in name_fakturs:
        if name in text:
            return True
    return False

def main():
    pdf_path = sys.argv[1]
    name_fakturs = sys.argv[2:]
    pdf_files = glob.glob(pdf_path)
    data_output = []

    for pdf_file in pdf_files:
        nama_faktur_found = extract_nama_faktur_from_pdf(pdf_file, name_fakturs)
        data_output.append(nama_faktur_found)

    if data_output:
        for data in data_output:
            print(json.dumps({"output": data}))
    else:
        print(json.dumps({"error": "Tidak ada data ditemukan"}))

if __name__ == "__main__":
    main()
