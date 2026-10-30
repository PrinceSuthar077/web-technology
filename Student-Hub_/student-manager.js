document.addEventListener("DOMContentLoaded", function () {
    const form = document.getElementById("studentForm");
    const formTitle = document.getElementById("formTitle");
    const idInput = document.getElementById("studentId");
    const nameInput = document.getElementById("studentName");
    const emailInput = document.getElementById("studentEmail");
    const courseSelect = document.getElementById("studentCourse");
    const submitBtn = document.getElementById("submitBtn");
    const cancelBtn = document.getElementById("cancelBtn");
    const searchInput = document.getElementById("searchInput");
    const courseFilter = document.getElementById("courseFilter");
    const clearBtn = document.getElementById("clearBtn");
    const filterForm = document.getElementById("filterForm");
    const rows = document.getElementById("studentRows");
    const loadingText = document.getElementById("loadingText");
    const statusMessage = document.getElementById("statusMessage");

    let students = [];
    let coursesLoaded = false;
    let requestCounter = 0;
    let searchTimer = null;

    function showMessage(text, type) {
        statusMessage.textContent = text;
        statusMessage.className = "alert " + type;
        statusMessage.hidden = false;
    }

    function setMode(editing) {
        formTitle.textContent = editing ? "Edit Student" : "Add New Student";
        submitBtn.textContent = editing ? "Update Student" : "Add Student";
        cancelBtn.hidden = !editing;
    }

    function resetForm() {
        form.reset();
        idInput.value = "";
        setMode(false);
    }

    async function apiRequest(url, options) {
        let response;

        try {
            response = await fetch(url, options);
        } catch (error) {
            throw new Error("Network error. Make sure Apache is running.");
        }

        if (response.status === 401) {
            window.location.href = "admin-login.php";
            throw new Error("Please log in as an admin.");
        }

        let data;

        try {
            data = await response.json();
        } catch (error) {
            throw new Error("Server returned an invalid response.");
        }

        if (!response.ok || !data.success) {
            throw new Error(data.message || "Request failed.");
        }

        return data;
    }

    function postAction(action, payload) {
        return apiRequest("api.php?action=" + action, {
            method: "POST",
            headers: { "Content-Type": "application/json" },
            body: JSON.stringify(payload)
        });
    }

    function fillCourses(courses) {
        courses.forEach(function (course) {
            courseSelect.appendChild(new Option(course, course));
            courseFilter.appendChild(new Option(course, course));
        });
        coursesLoaded = true;
    }

    function renderRows() {
        rows.textContent = "";

        if (students.length === 0) {
            const emptyRow = document.createElement("tr");
            const emptyCell = document.createElement("td");
            emptyCell.colSpan = 5;
            emptyCell.textContent = "No students found.";
            emptyRow.appendChild(emptyCell);
            rows.appendChild(emptyRow);
            return;
        }

        students.forEach(function (student) {
            const row = document.createElement("tr");

            [student.id, student.name, student.email, student.course].forEach(function (value) {
                const cell = document.createElement("td");
                cell.textContent = value;
                row.appendChild(cell);
            });

            const actionCell = document.createElement("td");
            const actions = document.createElement("div");
            actions.className = "table-actions";

            const editBtn = document.createElement("button");
            editBtn.type = "button";
            editBtn.textContent = "Edit";
            editBtn.dataset.action = "edit";
            editBtn.dataset.id = student.id;

            const deleteBtn = document.createElement("button");
            deleteBtn.type = "button";
            deleteBtn.className = "btn-danger";
            deleteBtn.textContent = "Delete";
            deleteBtn.dataset.action = "delete";
            deleteBtn.dataset.id = student.id;

            actions.appendChild(editBtn);
            actions.appendChild(deleteBtn);
            actionCell.appendChild(actions);
            row.appendChild(actionCell);
            rows.appendChild(row);
        });
    }

    async function loadStudents() {
        const currentRequest = ++requestCounter;
        loadingText.hidden = false;

        const params = new URLSearchParams({
            action: "list",
            search: searchInput.value.trim(),
            course: courseFilter.value
        });

        try {
            const data = await apiRequest("api.php?" + params.toString());

            if (currentRequest !== requestCounter) {
                return;
            }

            if (!coursesLoaded) {
                fillCourses(data.courses);
            }

            students = data.students;
            renderRows();
        } catch (error) {
            if (currentRequest === requestCounter) {
                showMessage(error.message, "error");
            }
        } finally {
            if (currentRequest === requestCounter) {
                loadingText.hidden = true;
            }
        }
    }

    function validate(payload) {
        if (payload.name === "" || payload.email === "" || payload.course === "") {
            return "Please fill in all fields.";
        }

        if (payload.name.length > 100) {
            return "Name must be 100 characters or less.";
        }

        if (!/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(payload.email)) {
            return "Enter a valid email address.";
        }

        return "";
    }

    form.addEventListener("submit", async function (event) {
        event.preventDefault();

        const payload = {
            name: nameInput.value.trim(),
            email: emailInput.value.trim(),
            course: courseSelect.value
        };

        const problem = validate(payload);

        if (problem !== "") {
            showMessage(problem, "error");
            return;
        }

        const editing = idInput.value !== "";
        submitBtn.disabled = true;
        submitBtn.textContent = "Saving...";

        try {
            let data;

            if (editing) {
                payload.id = Number(idInput.value);
                data = await postAction("update", payload);
            } else {
                data = await postAction("add", payload);
            }

            showMessage(data.message, "success");
            resetForm();
            await loadStudents();
        } catch (error) {
            showMessage(error.message, "error");
        } finally {
            submitBtn.disabled = false;
            setMode(idInput.value !== "");
        }
    });

    cancelBtn.addEventListener("click", function () {
        resetForm();
        statusMessage.hidden = true;
    });

    rows.addEventListener("click", async function (event) {
        const button = event.target.closest("button[data-action]");

        if (!button) {
            return;
        }

        const id = Number(button.dataset.id);

        if (button.dataset.action === "edit") {
            const student = students.find(function (item) {
                return Number(item.id) === id;
            });

            if (student) {
                idInput.value = student.id;
                nameInput.value = student.name;
                emailInput.value = student.email;
                courseSelect.value = student.course;
                setMode(true);
                statusMessage.hidden = true;
                form.scrollIntoView({ behavior: "smooth", block: "center" });
                nameInput.focus();
            }
            return;
        }

        if (!window.confirm("Delete this student?")) {
            return;
        }

        button.disabled = true;

        try {
            const data = await postAction("delete", { id: id });
            showMessage(data.message, "success");

            if (Number(idInput.value) === id) {
                resetForm();
            }

            await loadStudents();
        } catch (error) {
            showMessage(error.message, "error");
            button.disabled = false;
        }
    });

    searchInput.addEventListener("input", function () {
        clearTimeout(searchTimer);
        searchTimer = setTimeout(loadStudents, 300);
    });

    courseFilter.addEventListener("change", loadStudents);

    filterForm.addEventListener("submit", function (event) {
        event.preventDefault();
        loadStudents();
    });

    clearBtn.addEventListener("click", function () {
        searchInput.value = "";
        courseFilter.value = "";
        loadStudents();
    });

    loadStudents();
});
