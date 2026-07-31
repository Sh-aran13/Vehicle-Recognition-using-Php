# python/detect_plate.py
import json
import os
import re
import sys
import uuid

import cv2
from rapidocr_onnxruntime import RapidOCR


OCR_ENGINE = None


def get_ocr_engine():
    global OCR_ENGINE
    if OCR_ENGINE is None:
        OCR_ENGINE = RapidOCR()
    return OCR_ENGINE


def detect_plate_region(image):
    gray = cv2.cvtColor(image, cv2.COLOR_BGR2GRAY)
    filtered = cv2.bilateralFilter(gray, 11, 17, 17)
    edged = cv2.Canny(filtered, 30, 200)

    keypoints = cv2.findContours(edged.copy(), cv2.RETR_TREE, cv2.CHAIN_APPROX_SIMPLE)
    contours = keypoints[0] if len(keypoints) == 2 else keypoints[1]
    contours = sorted(contours, key=cv2.contourArea, reverse=True)[:15]

    for contour in contours:
        perimeter = cv2.arcLength(contour, True)
        approx = cv2.approxPolyDP(contour, 0.02 * perimeter, True)
        if len(approx) != 4:
            continue

        x, y, w, h = cv2.boundingRect(approx)
        if w < 60 or h < 20:
            continue

        ratio = w / float(h)
        if ratio < 1.8 or ratio > 7.5:
            continue

        pad = 12
        x1 = max(0, x - pad)
        y1 = max(0, y - pad)
        x2 = min(image.shape[1], x + w + pad)
        y2 = min(image.shape[0], y + h + pad)
        return image[y1:y2, x1:x2]

    return image


def prepare_for_ocr(image):
    gray = cv2.cvtColor(image, cv2.COLOR_BGR2GRAY)

    height, width = gray.shape[:2]
    scale = 2 if max(height, width) < 900 else 1
    if scale > 1:
        gray = cv2.resize(gray, None, fx=scale, fy=scale, interpolation=cv2.INTER_CUBIC)

    equalized = cv2.equalizeHist(gray)
    blurred = cv2.GaussianBlur(equalized, (3, 3), 0)
    threshold = cv2.adaptiveThreshold(
        blurred,
        255,
        cv2.ADAPTIVE_THRESH_GAUSSIAN_C,
        cv2.THRESH_BINARY,
        31,
        11,
    )

    return cv2.cvtColor(threshold, cv2.COLOR_GRAY2BGR)


def extract_plate_text(ocr_result):
    if not ocr_result:
        return ""

    candidates = []
    for item in ocr_result:
        if not item or len(item) < 2:
            continue

        text = re.sub(r"[^A-Z0-9]", "", str(item[1]).upper())
        if not text:
            continue

        score = float(item[2]) if len(item) > 2 else 0.0
        if 4 <= len(text) <= 12:
            candidates.append((score, len(text), text))

    if not candidates:
        return ""

    candidates.sort(key=lambda candidate: (candidate[0], candidate[1]), reverse=True)
    return candidates[0][2]


def run_ocr(image):
    engine = get_ocr_engine()
    prepared_variants = [prepare_for_ocr(image), image]

    best_text = ""
    best_score = -1.0

    for variant in prepared_variants:
        result, _ = engine(variant)
        if not result:
            continue

        for item in result:
            if not item or len(item) < 2:
                continue

            text = re.sub(r"[^A-Z0-9]", "", str(item[1]).upper())
            if not text:
                continue

            score = float(item[2]) if len(item) > 2 else 0.0
            if len(text) < 4:
                continue

            if score > best_score or (score == best_score and len(text) > len(best_text)):
                best_text = text
                best_score = score

    return best_text


def process_image(image_path):
    result = {
        "success": False,
        "plate_number": "UNKNOWN",
        "plate_path": ""
    }

    try:
        if not os.path.exists(image_path):
            result["error"] = "Input image was not found."
            return json.dumps(result)

        img = cv2.imread(image_path)
        if img is None:
            result["error"] = "Input image could not be opened."
            return json.dumps(result)

        plate_region = detect_plate_region(img)

        base_dir = os.path.dirname(os.path.dirname(os.path.abspath(__file__)))
        plates_dir = os.path.join(base_dir, "uploads", "plates")
        os.makedirs(plates_dir, exist_ok=True)

        plate_filename = f"plate_{uuid.uuid4().hex}.jpg"
        plate_storage_path = os.path.join(plates_dir, plate_filename)
        cv2.imwrite(plate_storage_path, plate_region)

        plate_text = run_ocr(plate_region)
        if plate_text:
            result["success"] = True
            result["plate_number"] = plate_text
            result["plate_path"] = "uploads/plates/" + plate_filename
        else:
            result["error"] = "No plate text could be read from the image."

        return json.dumps(result)
    except Exception as exc:
        result["error"] = str(exc)
        return json.dumps(result)


if __name__ == "__main__":
    if len(sys.argv) > 1:
        sys.stdout.write(process_image(sys.argv[1]))
    else:
        sys.stdout.write(json.dumps({"success": False, "error": "No image argument passed."}))