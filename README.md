# 🧩 Word Puzzle Grading System

This project is a Laravel-based backend service that allows students to submit word solutions from a given puzzle string. It evaluates each submission, ensures that the word is valid and not already used, and maintains a leaderboard with the highest scoring words.

---

## 🚀 Features

-   Validates user-submitted words against a dictionary
-   Ensures letters used match the original puzzle and are not reused
-   Scores each valid word
-   Stores submissions and displays a high-score leaderboard
-   Logging and debugging included for puzzle tracking

---

## 🛠️ Tech Stack

-   **Laravel 9+**
-   **PHP 8.1+**
-   **MySQL**
-   RESTful API support (for frontend integration or Postman testing)

---

## ✅ Why This Approach?

The project was designed with:

-   **Clean code separation**: Logic is moved to services and controllers.
-   **Scalability**: The system can easily support multiple users or puzzle sessions.
-   **Extensibility**: Easy to add authentication, multiplayer support, or advanced scoring rules.
-   **Testability**: Logic is isolated and ready for unit testing.
-   **Performance**: Dictionary is cached in memory to avoid repeated file reads.

The code prioritizes readability and adherence to Laravel conventions.

---

## 🧩 How Word Validation Works

1. A puzzle string (e.g. `YSb1YD9aavL3ng`) is stored in each `Game` session.
2. Each time a word is submitted:
    - It is checked against the dictionary (`words.txt`)
    - It is checked against used letters
    - It is verified to be constructible from the remaining puzzle letters
3. Valid submissions are saved and scored.

---

## ⚙️ Setup Instructions

1. **Clone the repository**

```bash
git clone https://github.com/smgopakumar/word-puzzle-system.git
cd word-puzzle-system
```


> ✅ **Tip**: Replace `https://github.com/yourusername/word-puzzle-system.git` with your actual GitHub repo URL.

---

Would you like me to generate this `README.md` as a downloadable file or push it directly into your project folder?

