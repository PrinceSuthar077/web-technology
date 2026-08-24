document.addEventListener("DOMContentLoaded", function () {

    const button = document.getElementById("darkModeBtn");
    const notification = document.getElementById("notification");
    const closeNotification = document.getElementById("closeNotification");

    if (localStorage.getItem("darkMode") === "on") {
        document.body.classList.add("dark-mode");

        if (button) {
            button.textContent = "Light Mode";
        }
    }

    if (button) {
        button.addEventListener("click", function () {

            document.body.classList.toggle("dark-mode");

            if (document.body.classList.contains("dark-mode")) {
                localStorage.setItem("darkMode", "on");
                button.textContent = "Light Mode";
            } else {
                localStorage.setItem("darkMode", "off");
                button.textContent = "Dark Mode";
            }

        });
    }

    if (closeNotification) {
        closeNotification.addEventListener("click", function () {
            notification.style.display = "none";
        });
    }


    const registrationForm = document.getElementById("registrationForm");

    if (registrationForm) {

        const fullName = document.getElementById("full-name");
        const studentId = document.getElementById("student-id");
        const email = document.getElementById("email");
        const phone = document.getElementById("phone");
        const course = document.getElementById("course");
        const semester = document.getElementById("semester");
        const password = document.getElementById("password");
        const confirmPassword = document.getElementById("confirm-password");
        const terms = document.getElementById("terms");

        const nameError = document.getElementById("nameError");
        const studentIdError = document.getElementById("studentIdError");
        const emailError = document.getElementById("emailError");
        const phoneError = document.getElementById("phoneError");
        const genderError = document.getElementById("genderError");
        const courseError = document.getElementById("courseError");
        const semesterError = document.getElementById("semesterError");
        const passwordError = document.getElementById("passwordError");
        const confirmPasswordError = document.getElementById("confirmPasswordError");
        const termsError = document.getElementById("termsError");

        const passwordStrength = document.getElementById("passwordStrength");
        const passwordStrengthBar = document.getElementById("passwordStrengthBar");
        const formMessage = document.getElementById("formMessage");


        function showError(input, errorElement, message) {

            errorElement.textContent = message;

            if (input) {
                input.classList.add("invalid");
                input.classList.remove("valid");
            }
        }


        function showSuccess(input, errorElement) {

            errorElement.textContent = "";

            if (input) {
                input.classList.remove("invalid");
                input.classList.add("valid");
            }
        }


        function validateName() {

            const nameRegex = /^[A-Za-z ]{3,50}$/;

            if (fullName.value.trim() === "") {
                showError(fullName, nameError, "Please enter your full name.");
                return false;
            }

            if (!nameRegex.test(fullName.value.trim())) {
                showError(
                    fullName,
                    nameError,
                    "Name should contain only letters and spaces."
                );
                return false;
            }

            showSuccess(fullName, nameError);
            return true;
        }


        function validateStudentId() {

            const studentIdRegex = /^[A-Za-z0-9-]{4,20}$/;

            if (studentId.value.trim() === "") {
                showError(
                    studentId,
                    studentIdError,
                    "Please enter your student ID."
                );
                return false;
            }

            if (!studentIdRegex.test(studentId.value.trim())) {
                showError(
                    studentId,
                    studentIdError,
                    "Student ID can contain letters, numbers and hyphens."
                );
                return false;
            }

            showSuccess(studentId, studentIdError);
            return true;
        }


        function validateEmail() {

            const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;

            if (email.value.trim() === "") {
                showError(
                    email,
                    emailError,
                    "Please enter your email address."
                );
                return false;
            }

            if (!emailRegex.test(email.value.trim())) {
                showError(
                    email,
                    emailError,
                    "Please enter a valid email address."
                );
                return false;
            }

            showSuccess(email, emailError);
            return true;
        }


        function validatePhone() {

            const phoneRegex = /^[6-9][0-9]{9}$/;

            if (phone.value.trim() === "") {
                showError(
                    phone,
                    phoneError,
                    "Please enter your mobile number."
                );
                return false;
            }

            if (!phoneRegex.test(phone.value.trim())) {
                showError(
                    phone,
                    phoneError,
                    "Enter a valid 10-digit mobile number."
                );
                return false;
            }

            showSuccess(phone, phoneError);
            return true;
        }


        function validateGender() {

            const gender = document.querySelector(
                'input[name="gender"]:checked'
            );

            if (!gender) {
                genderError.textContent = "Please select your gender.";
                return false;
            }

            genderError.textContent = "";
            return true;
        }


        function validateCourse() {

            if (course.value === "") {
                showError(
                    course,
                    courseError,
                    "Please select your course."
                );
                return false;
            }

            showSuccess(course, courseError);
            return true;
        }


        function validateSemester() {

            if (semester.value === "") {
                showError(
                    semester,
                    semesterError,
                    "Please select your semester."
                );
                return false;
            }

            showSuccess(semester, semesterError);
            return true;
        }


        function checkPasswordStrength() {

            const value = password.value;

            let strength = 0;

            if (value.length >= 8) {
                strength++;
            }

            if (/[A-Z]/.test(value)) {
                strength++;
            }

            if (/[a-z]/.test(value)) {
                strength++;
            }

            if (/[0-9]/.test(value)) {
                strength++;
            }

            if (/[^A-Za-z0-9]/.test(value)) {
                strength++;
            }


            if (value.length === 0) {

                passwordStrength.textContent = "Password strength";
                passwordStrengthBar.style.width = "0%";

            } else if (strength <= 2) {

                passwordStrength.textContent = "Weak password";
                passwordStrengthBar.style.width = "30%";

            } else if (strength === 3 || strength === 4) {

                passwordStrength.textContent = "Medium password";
                passwordStrengthBar.style.width = "65%";

            } else {

                passwordStrength.textContent = "Strong password";
                passwordStrengthBar.style.width = "100%";

            }

            return strength;
        }


        function validatePassword() {

            const passwordRegex =
                /^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[^A-Za-z0-9]).{8,}$/;

            if (password.value === "") {

                showError(
                    password,
                    passwordError,
                    "Please create a password."
                );

                return false;
            }

            if (!passwordRegex.test(password.value)) {

                showError(
                    password,
                    passwordError,
                    "Password must contain 8+ characters, uppercase, lowercase, number and special character."
                );

                return false;
            }

            showSuccess(password, passwordError);
            return true;
        }


        function validateConfirmPassword() {

            if (confirmPassword.value === "") {

                showError(
                    confirmPassword,
                    confirmPasswordError,
                    "Please confirm your password."
                );

                return false;
            }

            if (confirmPassword.value !== password.value) {

                showError(
                    confirmPassword,
                    confirmPasswordError,
                    "Passwords do not match."
                );

                return false;
            }

            showSuccess(
                confirmPassword,
                confirmPasswordError
            );

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

            checkPasswordStrength();

            if (password.value !== "") {
                validatePassword();
            }

            if (confirmPassword.value !== "") {
                validateConfirmPassword();
            }

        });


        confirmPassword.addEventListener("input", function () {
            validateConfirmPassword();
        });


        fullName.addEventListener("blur", validateName);
        studentId.addEventListener("blur", validateStudentId);
        email.addEventListener("blur", validateEmail);
        phone.addEventListener("blur", validatePhone);
        course.addEventListener("change", validateCourse);
        semester.addEventListener("change", validateSemester);
        terms.addEventListener("change", validateTerms);


        document.querySelectorAll('input[name="gender"]').forEach(function (radio) {

            radio.addEventListener("change", validateGender);

        });


        registrationForm.addEventListener("submit", function (event) {

            event.preventDefault();

            const validName = validateName();
            const validStudentId = validateStudentId();
            const validEmail = validateEmail();
            const validPhone = validatePhone();
            const validGender = validateGender();
            const validCourse = validateCourse();
            const validSemester = validateSemester();
            const validPassword = validatePassword();
            const validConfirmPassword = validateConfirmPassword();
            const validTerms = validateTerms();

            if (
                validName &&
                validStudentId &&
                validEmail &&
                validPhone &&
                validGender &&
                validCourse &&
                validSemester &&
                validPassword &&
                validConfirmPassword &&
                validTerms
            ) {

                formMessage.textContent =
                    "Registration successful! Your StudentHub account has been created.";

                formMessage.className = "form-message success";

                registrationForm.reset();

                document.querySelectorAll(
                    ".valid"
                ).forEach(function (element) {
                    element.classList.remove("valid");
                });

                passwordStrength.textContent = "Password strength";
                passwordStrengthBar.style.width = "0%";

            } else {

                formMessage.textContent =
                    "Please correct the errors above and try again.";

                formMessage.className = "form-message error";

                const firstError = registrationForm.querySelector(".invalid");

                if (firstError) {
                    firstError.focus();
                }

            }

        });

    }

});