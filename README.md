# 🏙️ UrbanaFix

<p align="center">
  <img src="https://img.shields.io/badge/PHP-777BB4?style=for-the-badge&logo=php&logoColor=white" alt="PHP" />
  <img src="https://img.shields.io/badge/JavaScript-F7DF1E?style=for-the-badge&logo=javascript&logoColor=black" alt="JavaScript" />
  <img src="https://img.shields.io/badge/HTML5-E34F26?style=for-the-badge&logo=html5&logoColor=white" alt="HTML5" />
  <img src="https://img.shields.io/badge/CSS3-1572B6?style=for-the-badge&logo=css3&logoColor=white" alt="CSS3" />
  <img src="https://img.shields.io/badge/MySQL-4479A1?style=for-the-badge&logo=mysql&logoColor=white" alt="MySQL" />
  <img src="https://img.shields.io/badge/Python-3776AB?style=for-the-badge&logo=python&logoColor=white" alt="Python" />
</p>

---

## 📝 About the Project

**UrbanaFix** is a web platform designed to connect citizens who want to report and help resolve urban issues. The main idea behind the project is to allow users to register and track city problems (such as potholes, street light failures, waste accumulation, etc.), fostering community engagement and local improvements.

> ℹ️ **Architecture Note:** The **Web Application** is the main platform of this project. The included **Python script** serves solely as an auxiliary desktop interface to control and manage the database.

---

## ✨ Main Features

- 🔐 **Authentication:** User registration and login system with session support.
- 📢 **Posting:** Creation and publishing of urban issue reports.
- 📰 **Community Feed:** Centralized feed displaying reports submitted by the community.
- 💬 **Interactions:** Like and comment system for user interaction.
- 🗄️ **Database Management:** Relational data storage using MySQL, featuring an auxiliary Python tool for database administration.

---

## 📌 Developer's Note

> **Important:** This was my **first full web project**. As an initial learning project, **it is currently not hosted live on the internet or on an active cloud server**. All development and testing were carried out in a local environment (such as XAMPP / Localhost).

---

## 💻 How to Run the Project Locally

1. **Install a Local Web Server:** Download and install [XAMPP](https://www.apachefriends.org/index.html) or [WAMP](https://www.wampserver.com/).
2. **Import the Database:** In the XAMPP control panel, open **phpMyAdmin** in your browser and import the `urbanafix.sql` file located at:
   ```text
   final technical project TCC/urbanafix_main_(web)/database/urbanafix.sql
