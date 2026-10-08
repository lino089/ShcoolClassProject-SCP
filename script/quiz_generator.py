import sys
import json

topic = sys.argv[1] if len(sys.argv) > 1 else "Materi Umum"

dummy_quiz = [
    {
        "question": f"Apa konsep utama dari pembahasan {topic}?",
        "options": {
            "A": "Konsep Dasar",
            "B": "Implementasi Lanjutan",
            "C": "Optimasi Database",
            "D": "Semua Benar"
        },
        "answer": "D"
    }
]

print(json.dumps(dummy_quiz))