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

    $("#myFile").on("change", function (e) {
        const [file] = this.files;

        if (file) {
            const objectUrl = URL.createObjectURL(file);
            $("#imgpreview img").attr("src", objectUrl);
            updateFileInputUI("myFile", "imgpreview", true);
        }
    });

    $("#gFile").on("change", function (e) {
        const files = Array.from(this.files);

        $("#galUpload").removeClass("up-load");

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

            $("#galUpload").before(galleryItem);
        });

        $("#galUpload").show();
    });

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

    if (imagePath) {
        $("#gallery-container").append(
            `<input type="hidden" name="remove_images[]" value="${imagePath}">`
        );
    }
    const remainingImages = $("#gallery-container .gitems").length;
    if (remainingImages === 0) {
        $(`#${inputId}`).val("");
        $("#galUpload").addClass("up-load");
    }
}

function removeUpload(previewId, inputId) {
    $("#" + previewId).hide();
    $("#" + previewId + " img").attr("src", "/images/upload/upload-1.png");
    $("#" + previewId + " p.file-name-overlay").remove();
    $("#" + previewId + " .remove-upload").hide();
    $("#" + inputId).val("");
    $("#upload-" + inputId).show();
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
