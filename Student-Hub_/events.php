<?php
session_start();

if (!isset($_SESSION["user_id"]) || ($_SESSION["role"] ?? "") !== "admin") {
    header("Location: admin-login.php");
    exit();
}

require_once "db.php";
require_once "layout.php";

$msg = "";
$msgType = "success";
$uploadDir = __DIR__ . "/uploads/";

if (!is_dir($uploadDir)) {
    if (!mkdir($uploadDir, 0755, true)) {
        die("Unable to create uploads folder.");
    }
}

function cleanText($value)
{
    return htmlspecialchars((string)$value, ENT_QUOTES, "UTF-8");
}

function removePoster($filename)
{
    $filename = basename((string)$filename);
    $path = __DIR__ . "/uploads/" . $filename;

    if ($filename !== "" && is_file($path)) {
        unlink($path);
    }
}

function getEventById($conn, $id)
{
    $stmt = mysqli_prepare($conn, "SELECT * FROM events WHERE id = ?");
    mysqli_stmt_bind_param($stmt, "i", $id);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    $row = mysqli_fetch_assoc($result);
    mysqli_stmt_close($stmt);
    return $row;
}

$form = [
    "id" => 0,
    "title" => "",
    "description" => "",
    "event_date" => "",
    "location" => "",
    "poster" => ""
];

if (isset($_POST["delete"])) {
    $id = (int)($_POST["id"] ?? 0);
    $event = getEventById($conn, $id);

    if ($event) {
        $stmt = mysqli_prepare($conn, "DELETE FROM events WHERE id = ?");
        mysqli_stmt_bind_param($stmt, "i", $id);

        if (mysqli_stmt_execute($stmt)) {
            removePoster($event["poster"]);
            $msg = "Event deleted successfully!";
        } else {
            $msg = "Unable to delete event.";
            $msgType = "error";
        }

        mysqli_stmt_close($stmt);
    } else {
        $msg = "Event not found.";
        $msgType = "error";
    }
}

if (isset($_POST["save"])) {
    $id          = (int)($_POST["id"] ?? 0);
    $title       = trim($_POST["title"] ?? "");
    $description = trim($_POST["description"] ?? "");
    $eventDate   = $_POST["event_date"] ?? "";
    $location    = trim($_POST["location"] ?? "");
    $oldPoster   = "";

    if ($id > 0) {
        $existing = getEventById($conn, $id);

        if (!$existing) {
            $msg = "Event not found.";
        } else {
            $oldPoster = $existing["poster"] ?? "";
        }
    }

    if ($msg === "") {
        if ($title === "" || $description === "" || $eventDate === "" || $location === "") {
            $msg = "Please fill in all event fields.";
        } else {
            $dateObject = DateTime::createFromFormat("!Y-m-d", $eventDate);

            if (!$dateObject || $dateObject->format("Y-m-d") !== $eventDate) {
                $msg = "Please enter a valid event date.";
            }
        }
    }

    $newPoster = "";
    $posterUploaded = false;

    if ($msg === "" && isset($_FILES["poster"]) && $_FILES["poster"]["error"] !== UPLOAD_ERR_NO_FILE) {
        $file = $_FILES["poster"];

        if ($file["error"] !== UPLOAD_ERR_OK) {
            $msg = "Poster upload failed. Please try again.";
        } elseif ($file["size"] > 2 * 1024 * 1024) {
            $msg = "Poster must be 2 MB or smaller.";
        } elseif (!is_uploaded_file($file["tmp_name"])) {
            $msg = "Invalid uploaded file.";
        } else {
            $info = @getimagesize($file["tmp_name"]);
            $mime = $info["mime"] ?? "";
            $allowedTypes = [
                "image/jpeg" => "jpg",
                "image/png"  => "png",
                "image/gif"  => "gif",
                "image/webp" => "webp"
            ];

            if ($info === false || !isset($allowedTypes[$mime])) {
                $msg = "Only valid JPG, PNG, GIF or WEBP images are allowed.";
            } else {
                $newPoster = bin2hex(random_bytes(16)) . "." . $allowedTypes[$mime];

                if (move_uploaded_file($file["tmp_name"], $uploadDir . $newPoster)) {
                    $posterUploaded = true;
                } else {
                    $msg = "Unable to save poster. Check the uploads folder.";
                }
            }
        }
    }

    if ($msg === "") {
        if ($id > 0) {
            if ($posterUploaded) {
                $stmt = mysqli_prepare(
                    $conn,
                    "UPDATE events SET title = ?, description = ?, event_date = ?, location = ?, poster = ? WHERE id = ?"
                );
                mysqli_stmt_bind_param($stmt, "sssssi", $title, $description, $eventDate, $location, $newPoster, $id);
            } else {
                $stmt = mysqli_prepare(
                    $conn,
                    "UPDATE events SET title = ?, description = ?, event_date = ?, location = ? WHERE id = ?"
                );
                mysqli_stmt_bind_param($stmt, "ssssi", $title, $description, $eventDate, $location, $id);
            }

            if (mysqli_stmt_execute($stmt)) {
                if ($posterUploaded) {
                    removePoster($oldPoster);
                }
                $msg = "Event updated successfully!";
            } else {
                $msg = "Unable to update event.";
                $msgType = "error";

                if ($posterUploaded) {
                    removePoster($newPoster);
                }
            }

            mysqli_stmt_close($stmt);
        } else {
            $stmt = mysqli_prepare(
                $conn,
                "INSERT INTO events (title, description, event_date, location, poster) VALUES (?, ?, ?, ?, ?)"
            );
            $posterValue = $posterUploaded ? $newPoster : null;
            mysqli_stmt_bind_param($stmt, "sssss", $title, $description, $eventDate, $location, $posterValue);

            if (mysqli_stmt_execute($stmt)) {
                $msg = "Event added successfully!";
            } else {
                $msg = "Unable to add event.";
                $msgType = "error";

                if ($posterUploaded) {
                    removePoster($newPoster);
                }
            }

            mysqli_stmt_close($stmt);
        }
    }

    if ($msgType === "success" && $msg !== "" && strpos($msg, "successfully") === false) {
        $msgType = "error";
    }

    if ($msgType === "error") {
        $form = [
            "id" => $id,
            "title" => $title,
            "description" => $description,
            "event_date" => $eventDate,
            "location" => $location,
            "poster" => $oldPoster
        ];
    }
}

if (isset($_GET["edit"]) && !isset($_POST["save"])) {
    $editEvent = getEventById($conn, (int)$_GET["edit"]);

    if ($editEvent) {
        $form = [
            "id" => (int)$editEvent["id"],
            "title" => $editEvent["title"],
            "description" => $editEvent["description"],
            "event_date" => $editEvent["event_date"],
            "location" => $editEvent["location"],
            "poster" => $editEvent["poster"] ?? ""
        ];
    }
}

$isEditing = $form["id"] > 0;

$search = trim($_GET["search"] ?? "");

if ($search !== "") {
    $searchValue = "%" . $search . "%";
    $stmt = mysqli_prepare(
        $conn,
        "SELECT * FROM events WHERE title LIKE ? OR location LIKE ? ORDER BY event_date DESC"
    );
    mysqli_stmt_bind_param($stmt, "ss", $searchValue, $searchValue);
} else {
    $stmt = mysqli_prepare($conn, "SELECT * FROM events ORDER BY event_date DESC");
}

mysqli_stmt_execute($stmt);
$events = mysqli_stmt_get_result($stmt);

renderHeader("Manage Events", "events");
?>
    <section class="page-header" aria-labelledby="page-title">
        <div class="container">
            <p class="section-label">ADMINISTRATION</p>
            <h1 id="page-title">Event Management</h1>
            <p>Add, edit, delete and search StudentHub events.</p>
        </div>
    </section>

    <section class="form-section" aria-labelledby="form-title">
        <div class="container">
            <?php if ($msg !== ""): ?>
                <div class="alert <?= $msgType ?>"><?= cleanText($msg) ?></div>
            <?php endif; ?>

            <form action="?" method="post" enctype="multipart/form-data" class="event-form">
                <h2 id="form-title"><?= $isEditing ? "Edit Event" : "Add New Event" ?></h2>

                <input type="hidden" name="id" value="<?= (int)$form["id"] ?>">

                <div class="form-group">
                    <label for="title">Event Title</label>
                    <input type="text" id="title" name="title" maxlength="150"
                           value="<?= cleanText($form["title"]) ?>" required>
                </div>

                <div class="form-group">
                    <label for="description">Description</label>
                    <textarea id="description" name="description" required><?= cleanText($form["description"]) ?></textarea>
                </div>

                <div class="form-group">
                    <label for="event_date">Event Date</label>
                    <input type="date" id="event_date" name="event_date"
                           value="<?= cleanText($form["event_date"]) ?>" required>
                </div>

                <div class="form-group">
                    <label for="location">Location</label>
                    <input type="text" id="location" name="location" maxlength="200"
                           value="<?= cleanText($form["location"]) ?>" required>
                </div>

                <div class="form-group">
                    <label for="poster">Event Poster</label>
                    <input type="file" id="poster" name="poster"
                           accept=".jpg,.jpeg,.png,.gif,.webp,image/jpeg,image/png,image/gif,image/webp">
                    <p class="input-help">JPG, PNG, GIF or WEBP. Maximum 2 MB.</p>
                </div>

                <?php if (!empty($form["poster"])): ?>
                    <div class="form-group">
                        <p>Current poster:</p>
                        <img class="current-poster"
                             src="<?= "uploads/" . rawurlencode(basename($form["poster"])) ?>"
                             alt="Current event poster">
                        <p class="input-help">Leave the poster field empty to keep the current poster.</p>
                    </div>
                <?php endif; ?>

                <div class="card-actions">
                    <button type="submit" name="save" class="btn"><?= $isEditing ? "Update Event" : "Add Event" ?></button>
                    <?php if ($isEditing): ?>
                        <a href="events.php" class="btn">Cancel</a>
                    <?php endif; ?>
                </div>
            </form>
        </div>
    </section>

    <section aria-labelledby="search-title">
        <div class="container">
            <div class="section-heading">
                <p class="section-label">FIND</p>
                <h2 id="search-title">Search Events</h2>
            </div>

            <form action="?" method="get" class="search-form">
                <input type="text" name="search" placeholder="Search by title or location"
                       value="<?= cleanText($search) ?>">
                <button type="submit" class="btn">Search</button>
                <a href="events.php" class="btn">Clear</a>
            </form>
        </div>
    </section>

    <section aria-labelledby="events-title">
        <div class="container">
            <div class="section-heading">
                <p class="section-label">ALL EVENTS</p>
                <h2 id="events-title">Event List</h2>
            </div>

            <?php if (mysqli_num_rows($events) > 0): ?>
                <div class="admin-event-grid">
                    <?php while ($event = mysqli_fetch_assoc($events)): ?>
                        <article>
                            <?php if (!empty($event["poster"])): ?>
                                <img class="event-poster"
                                     src="<?= "uploads/" . rawurlencode(basename($event["poster"])) ?>"
                                     alt="Poster for <?= cleanText($event["title"]) ?>">
                            <?php endif; ?>
                            <h3><?= cleanText($event["title"]) ?></h3>
                            <p><?= nl2br(cleanText($event["description"])) ?></p>
                            <p><strong>Date:</strong> <?= cleanText($event["event_date"]) ?></p>
                            <p><strong>Location:</strong> <?= cleanText($event["location"]) ?></p>

                            <div class="card-actions">
                                <a href="events.php?edit=<?= (int)$event["id"] ?>" class="btn">Edit</a>
                                <form action="?" method="post" class="inline-form"
                                      onsubmit="return confirm('Are you sure you want to delete this event?');">
                                    <input type="hidden" name="id" value="<?= (int)$event["id"] ?>">
                                    <button type="submit" name="delete" value="1">Delete</button>
                                </form>
                            </div>
                        </article>
                    <?php endwhile; ?>
                </div>
            <?php else: ?>
                <p>No events found.</p>
            <?php endif; ?>
        </div>
    </section>
<?php
renderFooter();
mysqli_stmt_close($stmt);
mysqli_close($conn);
