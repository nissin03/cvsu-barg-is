$(function () {
    const MAX_FILE_SIZE = 2 * 1024 * 1024; // 2MB
    const MAX_TOTAL_SIZE = 8 * 1024 * 1024; // 8MB
    const MAX_GALLERY_IMAGES = 3;
    const ALLOWED_IMAGE_TYPES = ["image/jpeg", "image/png", "image/jpg"];
    const ALLOWED_DOC_TYPES = [
        "application/pdf",
        "application/msword",
        "application/vnd.openxmlformats-officedocument.wordprocessingml.document",
        "image/jpeg",
        "image/png",
        "image/jpg",
        "application/vnd.ms-excel",
        "application/vnd.openxmlformats-officedocument.spreadsheetml.sheet",
        "text/plain",
        "application/rtf",
    ];

    // Function to validate file
    function validateFile(file, allowedTypes, options = {}) {
        const { checkType = true, checkSize = true } = options;

        if (checkType && !allowedTypes.includes(file.type)) {
            showAlert(
                `Invalid file type. Allowed types: ${allowedTypes.join(", ")}`,
                "danger"
            );
            return false;
        }

        if (checkSize && file.size > MAX_FILE_SIZE) {
            showAlert(`File size exceeds 2MB limit`, "danger");
            return false;
        }

        return true;
    }

    // Function to cleanup object URLs
    function cleanupObjectURLs() {
        const previews = document.querySelectorAll(
            ".gallery-preview img, #imgpreview img, #requirementsPreview img"
        );
        previews.forEach((img) => {
            if (img.src.startsWith("blob:")) {
                URL.revokeObjectURL(img.src);
            }
        });
    }

    // Function to clear validation errors for a specific input
    function clearInputValidation(inputId) {
        $(`#${inputId}`).removeClass("is-invalid");
        $(`#${inputId}`).next(".invalid-feedback").remove();
        $(".alert-danger").remove();
        $("#alertContainer").empty();
    }

    // Function to update file input UI
    function updateFileInputUI(inputId, previewId, showPreview = true) {
        const input = $(`#${inputId}`);
        const preview = $(`#${previewId}`);

        if (showPreview) {
            preview.show();
            preview.find(".remove-upload").show();
            $(`#upload-${inputId}`).hide();
        } else {
            preview.hide();
            preview.find(".remove-upload").hide();
            $(`#upload-${inputId}`).show();
        }
    }

    // Main Image Upload Handler
    $("#myFile").on("change", function (e) {
        clearInputValidation("myFile");
        const [file] = this.files;

        if (file) {
            if (!validateFile(file, ALLOWED_IMAGE_TYPES)) {
                this.value = "";
                return;
            }

            const objectUrl = URL.createObjectURL(file);
            $("#imgpreview img").attr("src", objectUrl);
            updateFileInputUI("myFile", "imgpreview", true);
        }
    });

    // Gallery Images Upload Handler
    $("#gFile").on("change", function (e) {
        clearInputValidation("gFile");
        const files = Array.from(this.files);

        if (files.length > MAX_GALLERY_IMAGES) {
            showAlert(`Maximum ${MAX_GALLERY_IMAGES} images allowed`, "danger");
            this.value = "";
            return;
        }

        let totalSize = 0;
        let validFiles = [];

        for (let file of files) {
            if (!validateFile(file, ALLOWED_IMAGE_TYPES)) {
                this.value = "";
                return;
            }
            totalSize += file.size;
            validFiles.push(file);
        }

        if (totalSize > MAX_TOTAL_SIZE) {
            showAlert("Total gallery size exceeds 8MB limit", "danger");
            this.value = "";
            return;
        }

        $("#galUpload").removeClass("up-load");

        // Process each valid file
        validFiles.forEach((file) => {
            const objectUrl = URL.createObjectURL(file);
            const galleryItem = $('<div class="item gitems">')
                .append(
                    $("<img>", {
                        src: objectUrl,
                        style: "width: 100px; height: 100px; object-fit: cover;",
                    })
                )
                .append($('<p class="file-name-overlay">').text(file.name))
                .append(
                    $("<button>", {
                        type: "button",
                        class: "remove-upload show",
                        text: "Remove",
                        click: function () {
                            removeGalleryImage(this, "gFile");
                        },
                    })
                );

            $("#galUpload").before(galleryItem);
        });

        $("#galUpload").show();
    });

    // Requirements File Upload Handler
    $("#requirementsFile").on("change", function (e) {
        clearInputValidation("requirementsFile");
        const [file] = this.files;

        if (file) {
            if (!validateFile(file, ALLOWED_DOC_TYPES, { checkType: false })) {
                this.value = "";
                return;
            }

            const objectUrl = URL.createObjectURL(file);
            $("#requirementsPreview img").attr("src", objectUrl);
            $("#requirementsPreview").show();
            $("#requirementsPreview .file-name-overlay").remove();
            $("#requirementsPreview").append(
                $('<p class="file-name-overlay">').text(file.name)
            );
            $("#requirementsPreview .remove-upload").show();
        }
    });

    // Cleanup object URLs when form is submitted or reset
    $("#facilityForm").on("submit reset", function () {
        cleanupObjectURLs();
    });
});

// Function to remove gallery image
function removeGalleryImage(button, inputId) {
    const img = $(button).parent(".gitems").find("img");
    if (img.length && img[0].src.startsWith("blob:")) {
        URL.revokeObjectURL(img[0].src);
    }
    $(button).parent(".gitems").remove();

    // Clear validation errors
    $(`#${inputId}`).removeClass("is-invalid");
    $(`#${inputId}`).next(".invalid-feedback").remove();
    $(".alert-danger").remove();
    $("#alertContainer").empty();

    // Update UI if no images left
    const remainingImages = $("#gallery-container .gitems").length;
    if (remainingImages === 0) {
        $(`#${inputId}`).val("");
        $("#galUpload").addClass("up-load");
    }
}

// Function to remove uploaded file
function removeUpload(previewId, inputId) {
    // Hide the preview block
    $("#" + previewId).hide();
    $("#" + previewId + " img").attr("src", "/images/upload/upload-1.png");
    $("#" + previewId + " p.file-name-overlay").remove();
    $("#" + previewId + " .remove-upload").hide();
    $("#" + inputId).val("");
    $("#upload-file").show();

    // Clear validation errors
    $("#" + inputId).removeClass("is-invalid");
    $("#" + inputId)
        .next(".invalid-feedback")
        .remove();
    $(".alert-danger").remove();
    $("#alertContainer").empty();
}

// Show alert function
function showAlert(message, type) {
    const alertBox = $("<div>", {
        class: `alert alert-${type} alert-dismissible fade show`,
        role: "alert",
        text: message,
    }).append(
        $("<button>", {
            type: "button",
            class: "btn-close",
            "data-bs-dismiss": "alert",
            "aria-label": "Close",
        })
    );

    $("#alertContainer").html(alertBox);
    alertBox.alert();
}

$(function () {
    tinymce.init({
        selector: "#rules",
        setup: function (editor) {
            editor.on("change", function (e) {
                tinyMCE.triggerSave();
                var sd_data = $("#short_description").val();
            });
        },
        height: 300,
        menubar: false,
        plugins: [
            "advlist",
            "autolink",
            "lists",
            "link",
            "image",
            "charmap",
            "preview",
            "anchor",
            "searchreplace",
            "visualblocks",
            "code",
            "fullscreen",
            "insertdatetime",
            "media",
            "table",
            "help",
            "wordcount",
        ],
        toolbar:
            "undo redo | blocks | " +
            "bold italic backcolor | alignleft aligncenter " +
            "alignright alignjustify | bullist numlist outdent indent | " +
            "removeformat | help",
    });
});

function StringToSlug(Text) {
    return Text.toLowerCase()
        .replace(/[^\w ]+/g, "")
        .replace(/ +/g, "-");
}
