$(function () {
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

    // Main image upload
    $("#myFile").on("change", function (e) {
        const [file] = this.files;

        if (file) {
            const objectUrl = URL.createObjectURL(file);
            $("#imgpreview img").attr("src", objectUrl);
            updateFileInputUI("myFile", "imgpreview", true);

            // Move upload area to bottom
            const container = $("#main-image-container");
            const uploadArea = $("#upload-file");
            container.append(uploadArea);
        }
    });

    // Gallery images upload
    $("#gFile").on("change", function (e) {
        const files = Array.from(this.files);
        const container = $("#gallery-container");
        const uploadArea = $("#galUpload");

        uploadArea.removeClass("up-load");

        files.forEach((file) => {
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

            // Insert before upload area to keep it at the end
            uploadArea.before(galleryItem);
        });

        // Add class to indicate images exist and move to bottom
        uploadArea.addClass("has-images");
        container.append(uploadArea);
        uploadArea.show();
    });

    // Requirements file upload
    $("#requirementsFile").on("change", function (e) {
        const [file] = this.files;

        if (file) {
            const objectUrl = URL.createObjectURL(file);
            $("#requirementsPreview img").attr("src", objectUrl);
            $("#requirementsPreview").show();
            $("#requirementsPreview .file-name-overlay").remove();
            $("#requirementsPreview").append(
                $('<p class="file-name-overlay">').text(file.name)
            );
            $("#requirementsPreview .remove-upload").show();

            // Move upload area to bottom
            const container = $("#requirements-container");
            const uploadArea = $("#upload-requirements");
            container.append(uploadArea);
        }
    });

    $(".form-add-rental").on("submit reset", function () {
        cleanupObjectURLs();
    });
});

function removeGalleryImage(button, inputId) {
    const img = $(button).parent(".gitems").find("img");
    if (img.length && img[0].src.startsWith("blob:")) {
        URL.revokeObjectURL(img[0].src);
    }
    $(button).parent(".gitems").remove();

    if (typeof imagePath !== "undefined" && imagePath) {
        $("#gallery-container").append(
            `<input type="hidden" name="remove_images[]" value="${imagePath}">`
        );
    }

    const remainingImages = $("#gallery-container .gitems").length;
    const uploadArea = $("#galUpload");

    if (remainingImages === 0) {
        $(`#${inputId}`).val("");
        uploadArea.addClass("up-load");
        uploadArea.removeClass("has-images");
        // Move upload area back to beginning when no images
        $("#gallery-container").prepend(uploadArea);
    } else {
        // Keep upload area at the end with full width
        uploadArea.addClass("has-images");
        $("#gallery-container").append(uploadArea);
    }
}

function removeUpload(previewId, inputId) {
    $("#" + previewId).hide();
    $("#" + previewId + " img").attr("src", "/images/upload/upload-1.png");
    $("#" + previewId + " .file-name-overlay").remove();
    $("#" + previewId + " .remove-upload").hide();
    $("#" + inputId).val("");
    $("#upload-" + inputId).show();

    // Move upload area back to beginning
    if (previewId === "requirementsPreview") {
        const container = $("#requirements-container");
        const uploadArea = $("#upload-requirements");
        container.prepend(uploadArea);
    } else if (previewId === "imgpreview") {
        const container = $("#main-image-container");
        const uploadArea = $("#upload-file");
        container.prepend(uploadArea);
    }
}

$(function () {
    tinymce.init({
        selector: "#rules",
        setup: function (editor) {
            editor.on("change", function (e) {
                tinyMCE.triggerSave();
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
