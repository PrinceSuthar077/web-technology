<?php
session_start();

if (!isset($_SESSION["user_id"]) || ($_SESSION["role"] ?? "") !== "admin") {
    header("Location: admin-login.php");
    exit();
}

require_once "layout.php";

renderHeader("Student Manager", "students", '<link rel="stylesheet" href="student-manager.css">');
?>
    <section class="page-header" aria-labelledby="page-title">
        <div class="container">
            <p class="section-label">ADMINISTRATION</p>
            <h1 id="page-title">Student Manager</h1>
            <p>Add, edit, delete and search students without reloading the page.</p>
        </div>
    </section>

    <section class="form-section" aria-labelledby="formTitle">
        <div class="container">
            <div id="statusMessage" class="alert" role="status" aria-live="polite" hidden></div>

            <form id="studentForm" class="event-form" novalidate>
                <h2 id="formTitle">Add New Student</h2>

                <input type="hidden" id="studentId">

                <div class="form-group">
                    <label for="studentName">Student Name</label>
                    <input type="text" id="studentName" maxlength="100" placeholder="Enter student name" required>
                </div>

                <div class="form-group">
                    <label for="studentEmail">Email</label>
                    <input type="email" id="studentEmail" maxlength="100" placeholder="Enter email address" required>
                </div>

                <div class="form-group">
                    <label for="studentCourse">Course</label>
                    <select id="studentCourse" required>
                        <option value="">Select Course</option>
                    </select>
                </div>

                <div class="card-actions">
                    <button type="submit" id="submitBtn" class="btn">Add Student</button>
                    <button type="button" id="cancelBtn" class="btn" hidden>Cancel</button>
                </div>
            </form>
        </div>
    </section>

    <section aria-labelledby="search-title">
        <div class="container">
            <div class="section-heading">
                <p class="section-label">FIND</p>
                <h2 id="search-title">Search and Filter</h2>
            </div>

            <form id="filterForm" class="search-form">
                <input type="text" id="searchInput" placeholder="Search by name or email">
                <select id="courseFilter">
                    <option value="">All Courses</option>
                </select>
                <button type="button" id="clearBtn" class="btn">Clear</button>
            </form>
        </div>
    </section>

    <section aria-labelledby="records-title">
        <div class="container">
            <div class="section-heading">
                <p class="section-label">RECORDS</p>
                <h2 id="records-title">Student Records</h2>
            </div>

            <p id="loadingText" class="loading-text" hidden>Loading students...</p>

            <div class="table-wrap">
                <table class="student-table">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Name</th>
                            <th>Email</th>
                            <th>Course</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody id="studentRows"></tbody>
                </table>
            </div>
        </div>
    </section>
<?php
renderFooter('<script src="student-manager.js"></script>');
