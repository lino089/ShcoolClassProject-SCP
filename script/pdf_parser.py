import sys
import json

file_kelas = sys.argv[1]
file_ruangan = sys.argv[2]

dummy_output = [
    {
        "kelas_mentah": "X RPL 1",
        "hari_mentah": "Senin",
        "jam_mentah": "1-2",
        "mapel_mentah": "MATEMATIKA",
        "guru_mentah": "Budi Santoso",
        "ruang_mentah": "Lab.19"
    }
];

print(json.dumps(dummy_output))