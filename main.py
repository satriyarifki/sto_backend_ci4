import fitz
from pyzbar.pyzbar import decode
from PIL import Image
import io
import json
import sys

def render_pages_as_images(pdf_path, zoom=2.0):
    pdf_document = fitz.open(pdf_path)
    images = []

    for page_number in range(len(pdf_document)):
        page = pdf_document.load_page(page_number)
        mat = fitz.Matrix(zoom, zoom)
        pix = page.get_pixmap(matrix=mat)
        img_data = pix.pil_tobytes(format="PNG")
        image = Image.open(io.BytesIO(img_data))
        images.append(image)

    return images

def decode_qr_from_images(images):
    qr_codes = []

    for image in images:
        decoded_objects = decode(image)
        for obj in decoded_objects:
            qr_codes.append(obj.data.decode('utf-8'))

    return qr_codes

def main():
    pdf_path = sys.argv[1]
    if not pdf_path:
        print("Tidak ada input yang diberikan")
        return

    # Ekstrak gambar dari PDF
    images = render_pages_as_images(pdf_path)

    # Decode QR code dari gambar yang diekstrak
    qr_codes = decode_qr_from_images(images)

    # Tampilkan hasil QR code
    if qr_codes:
        for qr_code in qr_codes:
            print(json.dumps({"output": qr_code}))
    else:
        print(json.dumps({"error": "Tidak ada QR code ditemukan"}))

if __name__ == "__main__":
    main()



    