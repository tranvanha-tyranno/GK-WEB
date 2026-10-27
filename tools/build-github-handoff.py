"""Split the existing baseline into disjoint member/round handoff packages.

This creates files and ZIPs only. It does not create student commits or push.
"""
from __future__ import annotations

import argparse
import csv
import hashlib
import json
from pathlib import Path
import shutil
import zipfile

from package import EXCLUDE, ROOT


ROLE = {
    "A": "HTTP, dang nhap va tich hop",
    "B": "Giao dien, client va thuyet trinh",
    "C": "Database, WebSocket va kiem thu",
}
MESSAGES = {
    "A": ["chore: import project scaffold and Docker baseline", "chore: import HTTP authentication and API baseline", "docs: import report and packaging material"],
    "B": ["chore: import PHP views and stylesheet baseline", "chore: import browser chat and reconnect client", "docs: import slides and presentation material"],
    "C": ["chore: import database and protocol baseline", "chore: import WebSocket services and server tests", "chore: import integration tools and experiment evidence"],
}
CHECKS = {
    "A": [
        "Đọc Dockerfile, Compose, Nginx /ws, config và bootstrap. Giải thích hai tiến trình PHP và volume SQLite; rà lệnh setup và dependency lock.",
        "Đọc login/me/logout, cookie/session/CSRF và API phòng/lịch sử. Khi đủ module sau lượt 6, kiểm tra login đúng/sai và logout; sau lượt 7 chạy test xác thực.",
        "Đối chiếu báo cáo với code thật, điền thông tin thật nếu có; thử cài sạch theo README và ghi kết quả vào docs/member-reviews/A.md. Không đổi log cũ thành lần chạy của mình.",
    ],
    "B": [
        "Đọc layout/login/chat và CSS responsive. Rà label, trạng thái và cách escape dữ liệu PHP. Bản ghép chưa chạy đầy đủ ở đợt này.",
        "Đọc api.js/socket.js/chat.js/app.js. Sau lượt 6 kiểm tra hai profile, đổi phòng, reconnect, history merge và desktop/mobile; ghi lỗi và sửa thật nếu phát hiện.",
        "Mở slide/ghi chú, rà thông tin với ứng dụng; tự tập thuyết trình, chụp minh chứng mới và ghi kết quả vào docs/member-reviews/B.md. Giữ phân biệt ảnh/log cũ với lần kiểm tra mới.",
    ],
    "C": [
        "Đọc schema, seed, repository và event contract. Giải thích khóa ngoại, query lịch sử và cấu trúc message; kiểm tra seed không tạo trùng sau khi đủ module.",
        "Đọc Gateway, RoomHub, rate limiter và ChatService. Giải thích kiểm tra quyền, lưu trước broadcast, presence/typing và heartbeat; rà unit test.",
        "Chạy PHPUnit/integration theo README trên stack test riêng sau lượt 7; đọc benchmark và giới hạn. Ghi lệnh, kết quả thật và lỗi còn lại vào docs/member-reviews/C.md.",
    ],
}


def source_paths() -> list[str]:
    paths = []
    for path in sorted(ROOT.rglob("*")):
        if not path.is_file():
            continue
        rel = path.relative_to(ROOT)
        if any(part in EXCLUDE for part in rel.parts):
            continue
        if path.suffix.lower() in {".sqlite", ".pyc", ".zip", ".dotx"}:
            continue
        if path.name.endswith((".sqlite-wal", ".sqlite-shm")):
            continue
        if path.name == "ProjectPlan-WebSockets-Redesigned.docx":
            continue
        paths.append(rel.as_posix())
    paths.append("storage/.gitkeep")
    for path in sorted((ROOT / "submission").rglob("*")):
        if path.is_file() and path.suffix.lower() != ".zip":
            paths.append(path.relative_to(ROOT).as_posix())
    return sorted(set(paths))


def owner(path: str) -> tuple[str, int]:
    if path.startswith(("resources/", "public/assets/css/")) or path == "public/assets/favicon.svg":
        return "B", 1
    if path.startswith("public/assets/js/") or path == "tests/manual-acceptance.md":
        return "B", 2
    if path.startswith(("src/Auth/", "src/Http/", "tests/Auth/")) or path in {
        "public/index.php", "src/Repositories/UserRepository.php", "src/Repositories/SessionRepository.php",
    }:
        return "A", 2
    if path.startswith(("database/", "src/Database/", "src/Shared/")) or path in {
        "src/Repositories/RoomRepository.php", "src/Repositories/MessageRepository.php",
        "docs/contracts.md", "storage/.gitkeep",
    }:
        return "C", 1
    if path.startswith(("src/Realtime/", "src/Services/", "src/Protocols/", "tests/Realtime/", "tests/Database/")) or path == "bin/ws-server.php":
        return "C", 2
    if path in {"src/Application.php", "phpunit.xml", "tests/TestCase.php"} or path.startswith(("bootstrap/", "config/", "deploy/")):
        return "A", 1
    if path.startswith("src/"):
        raise ValueError(f"New application module needs an explicit owner: {path}")
    if path.startswith("evidence/"):
        if path.startswith("evidence/screenshots/") or Path(path).name in {
            "browser-acceptance.json", "login-initialization.json", "protocol-capture.json",
            "reference-video.json", "ui-capture.json", "scene-alice.json", "walkthrough.json",
        }:
            return "B", 3
        return "C", 3
    if path.startswith("submission/"):
        if Path(path).name in {"slides.pptx", "slides.pdf", "reference-walkthrough.webm"} or path.startswith("submission/recording-kit/"):
            return "B", 3
        return "A", 3
    if path.startswith("docs/"):
        if Path(path).name in {"slides.json", "comparison.md", "demo-script.md", "qa-questions.md", "report-review.md"}:
            return "B", 3
        if Path(path).suffix == ".mmd" or Path(path).name in {"test-cases.md", "experiment-plan.md"}:
            return "C", 3
        if path == "docs/github-handoff.md":
            return "A", 1
        return "A", 3
    if path.startswith("tools/"):
        if Path(path).name in {"build-github-handoff.py", "import-github-round.ps1", "setup.ps1", "lint.php", "requirements.txt"}:
            return "A", 1
        if Path(path).suffix == ".js" or Path(path).name == "run-browser-check.py":
            return "B", 3
        if path.startswith("tools/benchmark/") or Path(path).name in {"integration.py", "concurrent.py", "verify-runtime.py"}:
            return "C", 3
        if Path(path).name == "build-slides.ps1":
            return "B", 3
        return "A", 3
    if path.startswith(("tests/", "test-data/")):
        return "C", 3
    if path == "PHAN_CONG_PHP_3_NGUOI.md":
        return "A", 3
    if "/" not in path or path == "bin/reset-demo.php":
        return "A", 1
    raise ValueError(f"Unassigned file: {path}")


def write_text(path: Path, text: str) -> None:
    path.parent.mkdir(parents=True, exist_ok=True)
    path.write_text(text, encoding="utf-8")


def main() -> None:
    parser = argparse.ArgumentParser(description=__doc__)
    parser.add_argument("--output", default="github-handoff")
    args = parser.parse_args()
    target = (ROOT / args.output).resolve()
    allowed = {ROOT / "github-handoff"}
    if target not in allowed and not target.is_relative_to(ROOT / "tmp"):
        raise ValueError("Output must be github-handoff or a new directory under tmp/.")
    if target.exists():
        raise FileExistsError(f"Output already exists; keep it and choose --output tmp/github-handoff-v2: {target}")

    paths = source_paths()
    contents = {path: (ROOT / path).read_bytes() for path in paths}
    assignments = {path: owner(path) for path in paths}
    for member in ROLE:
        path = f"docs/member-reviews/{member}.md"
        if path in assignments:
            raise ValueError(f"Review path already exists; assign it explicitly before rebuilding: {path}")
        contents[path] = (
            f"# Member {member}: actual review and contribution record\n\n"
            "Fill only after doing the work. The imported baseline and old logs are not a new test run.\n\n"
            "| Field | Actual value |\n| --- | --- |\n"
            "| Full name / student ID | |\n| GitHub username | |\n| Review date | |\n"
            "| Files read and understood | |\n| Changes personally made | |\n"
            "| Commands actually run | |\n| Results and new evidence paths | |\n"
            "| Remaining defects / limits | |\n| Commit / PR URLs | |\n"
            "| Presentation section / timestamp | |\n"
        ).encode("utf-8")
        assignments[path] = member, 3

    # Finish validation before creating the output directory.
    assert len(assignments) == len(contents)
    assert all(any(value == (member, round_) for value in assignments.values()) for member in ROLE for round_ in (1, 2, 3))
    target.mkdir(parents=True)
    rows = []
    summary = {"source_files": len(paths), "review_templates": 3, "payload_files": len(contents), "members": {}}
    shared_guide = (ROOT / "docs/github-handoff.md").read_text(encoding="utf-8")
    for member, role in ROLE.items():
        package_root = target / f"member-{member}"
        write_text(package_root / "package.json", json.dumps({"member": member, "role": role, "rounds": 3}, indent=2))
        shutil.copy2(ROOT / "tools/import-github-round.ps1", package_root / "Import-Round.ps1")
        write_text(package_root / "HUONG_DAN_CHUNG.md", shared_guide)
        readme = [f"# Thành viên {member} — {role}", "", "Dùng một repo chung cho ba người. Đọc HUONG_DAN_CHUNG.md trước khi nhập. Không git init trong thư mục gói này.", "", "## Phần việc theo đợt", ""]
        round_counts = {}
        for round_ in (1, 2, 3):
            round_root = package_root / f"dot-{round_:02d}"
            entries = []
            for path in sorted(contents):
                if assignments[path] != (member, round_):
                    continue
                payload = contents[path]
                output = round_root / "files" / path
                output.parent.mkdir(parents=True, exist_ok=True)
                output.write_bytes(payload)
                entry = {"path": path, "bytes": len(payload), "sha256": hashlib.sha256(payload).hexdigest()}
                entries.append(entry)
                rows.append({"member": member, "round": round_, **entry})
            manifest = {"member": member, "round": round_, "commit_message": MESSAGES[member][round_ - 1], "files": entries}
            write_text(round_root / "manifest.json", json.dumps(manifest, ensure_ascii=False, indent=2) + "\n")
            round_counts[str(round_)] = len(entries)
            readme += [f"### Đợt {round_} — {len(entries)} file", "", CHECKS[member][round_ - 1], "", f"Commit nhập gợi ý: `{MESSAGES[member][round_ - 1]}`", "", "File:", ""]
            readme += [f"- `{entry['path']}`" for entry in entries]
            readme += [""]
        write_text(package_root / "README.md", "\n".join(readme) + "\n")
        archive = target / f"member-{member}.zip"
        with zipfile.ZipFile(archive, "w", zipfile.ZIP_DEFLATED) as out:
            for path in sorted(package_root.rglob("*")):
                if path.is_file():
                    out.write(path, path.relative_to(target).as_posix())
        with zipfile.ZipFile(archive) as check:
            assert check.testzip() is None, archive
        summary["members"][member] = {"round_files": round_counts, "zip_bytes": archive.stat().st_size}

    with (target / "ownership.csv").open("w", encoding="utf-8-sig", newline="") as stream:
        writer = csv.DictWriter(stream, fieldnames=["member", "round", "path", "bytes", "sha256"])
        writer.writeheader()
        writer.writerows(rows)
    assert len(rows) == len({row["path"] for row in rows}) == len(contents)
    write_text(target / "README.md", shared_guide)
    write_text(target / "verification.json", json.dumps(summary, indent=2) + "\n")
    print(json.dumps({"output": str(target), **summary}, indent=2))


if __name__ == "__main__":
    main()
