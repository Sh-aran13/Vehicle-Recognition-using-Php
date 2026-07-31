<div align="center">

# 🚗 Vehicle Recognition using PHP

### AI-Powered Vehicle Number Plate Detection & Recognition System

[![PHP](https://img.shields.io/badge/PHP-8.x-777BB4?style=for-the-badge&logo=php&logoColor=white)]()
[![Python](https://img.shields.io/badge/Python-3.x-3776AB?style=for-the-badge&logo=python&logoColor=white)]()
[![PostgreSQL](https://img.shields.io/badge/PostgreSQL-Database-4479A1?style=for-the-badge&logo=postgresql&logoColor=white)]()
[![OpenCV](https://img.shields.io/badge/OpenCV-Computer%20Vision-5C3EE8?style=for-the-badge&logo=opencv&logoColor=white)]()
[![RapidOCR](https://img.shields.io/badge/RapidOCR-OCR-success?style=for-the-badge)]()

A modern web-based **Automatic Number Plate Recognition (ANPR)** system built using **PHP**, **Python**, **OpenCV**, and **RapidOCR**.  
The application detects vehicle license plates from uploaded images, extracts the registration number using OCR, stores scan history, and provides a secure user authentication system.

</div>

---

# 📖 Overview

Vehicle Recognition using PHP is an intelligent web application that combines traditional PHP web development with Artificial Intelligence powered image processing.

The system allows authenticated users to upload vehicle images, automatically detects the license plate using OpenCV, extracts the text using RapidOCR, and stores every scan in a database for future reference.

The project demonstrates the integration of

- PHP
- Python
- OpenCV
- OCR
- MySQL
- Secure Authentication

into one complete real-world application.

---

# ✨ Features

## 🔐 Authentication

- Secure User Registration
- Secure Login System
- Password Hashing (bcrypt)
- Session Management
- Protected Dashboard
- Logout Functionality

---

## 🚗 Vehicle Detection

- Upload Vehicle Images
- Automatic Number Plate Detection
- Plate Cropping
- OCR-based Plate Recognition
- High Accuracy Text Extraction
- Supports JPG, JPEG and PNG images

---

## 📊 Scan History

- Store all scans
- View previous detections
- Vehicle image preview
- Plate image preview
- Extracted number display
- Delete previous records

---

## ⚡ AI Processing

- OpenCV based Number Plate Localization
- Contour Detection
- Image Preprocessing
- RapidOCR Text Recognition
- Automatic Plate Image Generation

---

## 🎨 User Interface

- Modern Dark Theme
- Responsive Design
- Smooth Animations
- Professional Dashboard
- Mobile Friendly

---

# 🖼 Workflow

```text
Upload Vehicle Image
          │
          ▼
PHP Upload API
          │
          ▼
Python Detection Script
          │
          ▼
OpenCV detects Number Plate
          │
          ▼
RapidOCR extracts Plate Text
          │
          ▼
Store Images + OCR Result
          │
          ▼
Display Result to User
```

---

# 🛠 Tech Stack

## Frontend

- HTML5
- CSS3
- JavaScript

## Backend

- PHP

## AI / Computer Vision

- Python
- OpenCV
- RapidOCR

## Database

- MySQL

---

# 📂 Project Structure

```text
vehicle-recognition/

│
├── api/
│   └── detect.php
│
├── assets/
│   ├── css/
│   └── js/
│
├── database/
│   └── db.php
│
├── python/
│   ├── detect_plate.py
│   └── models/
│
├── uploads/
│   ├── vehicles/
│   └── plates/
│
├── index.php
├── login.php
├── register.php
├── dashboard.php
├── history.php
├── upload.php
└── logout.php
```

---

# 🚀 Installation

## Clone Repository

```bash
git clone https://github.com/Sh-aran13/Vehicle-Recognition-using-Php.git
```

---

## Move into Project

```bash
cd Vehicle-Recognition-using-Php
```

---

## Install Python Dependencies

```bash
pip install opencv-python
pip install rapidocr-onnxruntime
```

---

## Configure Database

Create a MySQL database.

Update database credentials inside

```text
database/db.php
```

---

## Start PHP Server

```bash
php -S localhost:8000
```

or place the project inside

```text
htdocs
```

if using XAMPP.

---

# 📸 Screens

The project includes

- Landing Page
- Login Page
- Registration Page
- Detection Dashboard
- Upload Interface
- Scan History
- OCR Results

---

# 🔒 Security Features

- Password Hashing using bcrypt
- Prepared SQL Statements
- Session Protection
- Input Validation
- Secure Authentication
- File Type Validation

---

# 🎯 Future Improvements

- Live Camera Detection
- Video Stream Recognition
- Real-time Vehicle Tracking
- Multi-language OCR
- Admin Dashboard
- User Roles
- REST API
- Cloud Storage
- Docker Support
- Automatic Vehicle Classification

---

# 💡 Applications

- Smart Parking Systems
- Toll Collection
- Traffic Monitoring
- Campus Vehicle Management
- Apartment Security
- Vehicle Entry Automation
- Law Enforcement
- Industrial Access Control

---

# 🤝 Contributing

Contributions are welcome.

1. Fork the repository

2. Create a new branch

```bash
git checkout -b feature-name
```

3. Commit changes

```bash
git commit -m "Added new feature"
```

4. Push

```bash
git push origin feature-name
```

5. Open a Pull Request

---

# 👨‍💻 Author

## Sharan

**GitHub**

https://github.com/Sh-aran13

---

# ⭐ Support

If you found this project useful, please consider giving it a ⭐ on GitHub.

It helps others discover the project and motivates future improvements.

---

<div align="center">

### ⭐ Star • 🍴 Fork • 🚀 Learn • ❤️ Build

**Made with PHP, Python, OpenCV & RapidOCR**

</div>
