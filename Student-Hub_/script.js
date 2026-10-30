document.addEventListener("DOMContentLoaded", function () {


const darkModeBtn = document.getElementById("darkModeBtn");
const closeNotification = document.getElementById("closeNotification");
const notification = document.getElementById("notification");

if (localStorage.getItem("darkMode") === "on") {
    document.body.classList.add("dark-mode");
    if (darkModeBtn) darkModeBtn.textContent = "Light Mode";
}

if (darkModeBtn) {
    darkModeBtn.addEventListener("click", function () {
        document.body.classList.toggle("dark-mode");

        if (document.body.classList.contains("dark-mode")) {
            localStorage.setItem("darkMode", "on");
            darkModeBtn.textContent = "Light Mode";
        } else {
            localStorage.setItem("darkMode", "off");
            darkModeBtn.textContent = "Dark Mode";
        }
    });
}

if (closeNotification) {
    closeNotification.addEventListener("click", function () {
        notification.style.display = "none";
    });
}

const eventsContainer = document.getElementById("eventsContainer");

if (eventsContainer) {

    const searchInput = document.getElementById("searchInput");
    const categoryButtons =
        document.querySelectorAll(".category-btn");
    const sortSelect = document.getElementById("sortSelect");
    const loadingMessage =
        document.getElementById("loadingMessage");
    const pagination =
        document.getElementById("pagination");

    let allEvents = [];
    let category = "All";
    let sort = "default";
    let page = 1;
    const perPage = 2;

    function showEvents() {

        let events = allEvents.filter(function (event) {

            return (
                (category === "All" ||
                    event.category === category) &&
                (
                    event.title.toLowerCase()
                        .includes(searchInput.value.toLowerCase()) ||
                    event.description.toLowerCase()
                        .includes(searchInput.value.toLowerCase())
                )
            );

        });

        if (sort === "dateAsc") {
            events.sort(function (a, b) {
                return new Date(a.date) - new Date(b.date);
            });
        }

        if (sort === "dateDesc") {
            events.sort(function (a, b) {
                return new Date(b.date) - new Date(a.date);
            });
        }

        const totalPages =
            Math.ceil(events.length / perPage);

        if (page > totalPages) page = 1;

        const start = (page - 1) * perPage;
        const data = events.slice(start, start + perPage);

        eventsContainer.innerHTML = "";

        if (data.length === 0) {
            eventsContainer.innerHTML =
                "<p>No events found.</p>";
        }

        data.forEach(function (event) {

            const date = new Date(event.date);

            eventsContainer.innerHTML += `
                <article class="event-card">

                    <div class="event-date">
                        <strong>${date.getDate()}</strong>

                        <span>
                            ${date.toLocaleString("en-US", {
                                month: "short"
                            }).toUpperCase()}
                        </span>
                    </div>

                    <div class="event-details">

                        <span class="event-type">
                            ${event.category}
                        </span>

                        <h3>${event.title}</h3>

                        <p>${event.description}</p>

                        <p>
                            <strong>Date:</strong>
                            ${event.date}
                        </p>

                        <p>
                            <strong>Time:</strong>
                            ${event.time}
                        </p>

                        <p>
                            <strong>Venue:</strong>
                            ${event.venue}
                        </p>

                    </div>

                    <a href="login.html"
                        class="btn primary-btn">
                        Register
                    </a>

                </article>
            `;
        });

        pagination.innerHTML = "";

        for (let i = 1; i <= totalPages; i++) {

            pagination.innerHTML += `
                <button class="page-btn"
                    data-page="${i}">
                    ${i}
                </button>
            `;
        }

        document.querySelectorAll(".page-btn").forEach(
            function (button) {

                button.addEventListener("click", function () {

                    page = Number(button.dataset.page);

                    showEvents();

                });

            }
        );
    }

    fetch("JSON/events.json")
        .then(function (response) {

            if (!response.ok) {
                throw new Error("JSON file not found");
            }

            return response.json();

        })
        .then(function (data) {

            allEvents = data;

            loadingMessage.style.display = "none";

            showEvents();

        })
        .catch(function () {

            loadingMessage.textContent =
                "Unable to load events.";

        });

    searchInput.addEventListener("input", function () {

        page = 1;
        showEvents();

    });

    categoryButtons.forEach(function (button) {

        button.addEventListener("click", function () {

            category = button.dataset.category;

            page = 1;

            showEvents();

        });

    });

    sortSelect.addEventListener("change", function () {

        sort = sortSelect.value;

        page = 1;

        showEvents();

    });
}

const registrationForm =
    document.getElementById("registrationForm");

if (registrationForm) {

    const fullName =
        document.getElementById("full-name");

    const studentId =
        document.getElementById("student-id");

    const email =
        document.getElementById("email");

    const phone =
        document.getElementById("phone");

    const course =
        document.getElementById("course");

    const semester =
        document.getElementById("semester");

    const password =
        document.getElementById("password");

    const confirmPassword =
        document.getElementById("confirm-password");

    const terms =
        document.getElementById("terms");

    const nameError =
        document.getElementById("nameError");

    const studentIdError =
        document.getElementById("studentIdError");

    const emailError =
        document.getElementById("emailError");

    const phoneError =
        document.getElementById("phoneError");

    const genderError =
        document.getElementById("genderError");

    const courseError =
        document.getElementById("courseError");

    const semesterError =
        document.getElementById("semesterError");

    const passwordError =
        document.getElementById("passwordError");

    const confirmPasswordError =
        document.getElementById("confirmPasswordError");

    const termsError =
        document.getElementById("termsError");

    const passwordStrength =
        document.getElementById("passwordStrength");

    const passwordStrengthBar =
        document.getElementById("passwordStrengthBar");

    const formMessage =
        document.getElementById("formMessage");

    function error(input, element, message) {

        element.textContent = message;

        if (input) {
            input.classList.add("invalid");
            input.classList.remove("valid");
        }

    }

    function success(input, element) {

        element.textContent = "";

        if (input) {
            input.classList.remove("invalid");
            input.classList.add("valid");
        }

    }

    function validateName() {

        const regex = /^[A-Za-z ]{3,50}$/;

        if (!fullName.value.trim())
            return error(
                fullName,
                nameError,
                "Please enter your full name."
            ) || false;

        if (!regex.test(fullName.value.trim()))
            return error(
                fullName,
                nameError,
                "Name should contain only letters and spaces."
            ) || false;

        success(fullName, nameError);
        return true;
    }

    function validateStudentId() {

        const regex = /^[A-Za-z0-9-]{4,20}$/;

        if (!studentId.value.trim())
            return error(
                studentId,
                studentIdError,
                "Please enter your student ID."
            ) || false;

        if (!regex.test(studentId.value.trim()))
            return error(
                studentId,
                studentIdError,
                "Student ID can contain letters, numbers and hyphens."
            ) || false;

        success(studentId, studentIdError);
        return true;
    }

    function validateEmail() {

        const regex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;

        if (!email.value.trim())
            return error(
                email,
                emailError,
                "Please enter your email address."
            ) || false;

        if (!regex.test(email.value.trim()))
            return error(
                email,
                emailError,
                "Please enter a valid email address."
            ) || false;

        success(email, emailError);
        return true;
    }

    function validatePhone() {

        const regex = /^[6-9][0-9]{9}$/;

        if (!phone.value.trim())
            return error(
                phone,
                phoneError,
                "Please enter your mobile number."
            ) || false;

        if (!regex.test(phone.value.trim()))
            return error(
                phone,
                phoneError,
                "Enter a valid 10-digit mobile number."
            ) || false;

        success(phone, phoneError);
        return true;
    }

    function validateGender() {

        const gender =
            document.querySelector(
                'input[name="gender"]:checked'
            );

        if (!gender) {
            genderError.textContent =
                "Please select your gender.";
            return false;
        }

        genderError.textContent = "";
        return true;
    }

    function validateCourse() {

        if (course.value === "") {
            error(
                course,
                courseError,
                "Please select your course."
            );
            return false;
        }

        success(course, courseError);
        return true;
    }

    function validateSemester() {

        if (semester.value === "") {
            error(
                semester,
                semesterError,
                "Please select your semester."
            );
            return false;
        }

        success(semester, semesterError);
        return true;
    }

    function passwordStrength() {

        const value = password.value;
        let strength = 0;

        if (value.length >= 8) strength++;
        if (/[A-Z]/.test(value)) strength++;
        if (/[a-z]/.test(value)) strength++;
        if (/[0-9]/.test(value)) strength++;
        if (/[^A-Za-z0-9]/.test(value)) strength++;

        if (!value) {
            passwordStrength.textContent =
                "Password strength";
            passwordStrengthBar.style.width = "0%";
        } else if (strength <= 2) {
            passwordStrength.textContent =
                "Weak password";
            passwordStrengthBar.style.width = "30%";
        } else if (strength <= 4) {
            passwordStrength.textContent =
                "Medium password";
            passwordStrengthBar.style.width = "65%";
        } else {
            passwordStrength.textContent =
                "Strong password";
            passwordStrengthBar.style.width = "100%";
        }
    }

    function validatePassword() {

        const regex =
            /^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[^A-Za-z0-9]).{8,}$/;

        if (!password.value)
            return error(
                password,
                passwordError,
                "Please create a password."
            ) || false;

        if (!regex.test(password.value))
            return error(
                password,
                passwordError,
                "Password must contain 8+ characters, uppercase, lowercase, number and special character."
            ) || false;

        success(password, passwordError);
        return true;
    }

    function validateConfirmPassword() {

        if (!confirmPassword.value)
            return error(
                confirmPassword,
                confirmPasswordError,
                "Please confirm your password."
            ) || false;

        if (confirmPassword.value !== password.value)
            return error(
                confirmPassword,
                confirmPasswordError,
                "Passwords do not match."
            ) || false;

        success(confirmPassword, confirmPasswordError);
        return true;
    }

    function validateTerms() {

        if (!terms.checked) {
            termsError.textContent =
                "You must accept the terms and conditions.";
            return false;
        }

        termsError.textContent = "";
        return true;
    }

    password.addEventListener("input", function () {

        passwordStrength();

        if (password.value)
            validatePassword();

        if (confirmPassword.value)
            validateConfirmPassword();

    });

    confirmPassword.addEventListener(
        "input",
        validateConfirmPassword
    );

    fullName.addEventListener("blur", validateName);
    studentId.addEventListener("blur", validateStudentId);
    email.addEventListener("blur", validateEmail);
    phone.addEventListener("blur", validatePhone);
    course.addEventListener("change", validateCourse);
    semester.addEventListener("change", validateSemester);
    terms.addEventListener("change", validateTerms);

    document.querySelectorAll(
        'input[name="gender"]'
    ).forEach(function (radio) {

        radio.addEventListener(
            "change",
            validateGender
        );

    });

    registrationForm.addEventListener(
        "submit",
        function (event) {

            event.preventDefault();

            const valid =
                validateName() &&
                validateStudentId() &&
                validateEmail() &&
                validatePhone() &&
                validateGender() &&
                validateCourse() &&
                validateSemester() &&
                validatePassword() &&
                validateConfirmPassword() &&
                validateTerms();

            if (valid) {

                formMessage.textContent =
                    "Registration successful! Your StudentHub account has been created.";

                formMessage.className =
                    "form-message success";

                registrationForm.reset();

                document.querySelectorAll(
                    ".valid"
                ).forEach(function (element) {
                    element.classList.remove("valid");
                });

                passwordStrength.textContent =
                    "Password strength";

                passwordStrengthBar.style.width =
                    "0%";

            } else {

                formMessage.textContent =
                    "Please correct the errors above and try again.";

                formMessage.className =
                    "form-message error";

                const firstError =
                    registrationForm.querySelector(".invalid");

                if (firstError)
                    firstError.focus();
            }
        }
    );
}
```

});
