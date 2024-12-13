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


def decode_qr_from_images(images, target_prefix=None):
    qr_codes = []

    for image in images:
        decoded_objects = decode(image)
        for obj in decoded_objects:
            qr_code_data = obj.data.decode('utf-8')

            try:
                qr_data_json = json.loads(qr_code_data)
                
                if target_prefix:
                    id_qr = qr_data_json.get("id_qr", "")
                    if id_qr.startswith(target_prefix):
                        qr_codes.append(qr_data_json)
                else:
                    qr_codes.append(qr_data_json)
            except json.JSONDecodeError:
                continue

    return qr_codes


def main():
    pdf_path = sys.argv[1]
    target_prefix = "MAJ"

    if not pdf_path:
        print(json.dumps({"error": "Tidak ada input yang diberikan"}))
        return

    images = render_pages_as_images(pdf_path)

    qr_codes = decode_qr_from_images(images, target_prefix)

    if qr_codes:
        print(json.dumps({"output": qr_codes[0]}))
    else:
        print(json.dumps({"error": "Tidak ada QR code yang sesuai ditemukan"}))

if __name__ == "__main__":
    main()