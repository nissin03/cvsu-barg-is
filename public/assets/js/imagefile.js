$(function () {
    $("#myFile").on("change", function (e) {
        const [file] = this.files;
        if (file) {
            $("#imgpreview img").attr("src", URL.createObjectURL(file));
            $("#imgpreview").show();
            $("#imgpreview .remove-upload").show();
            $("#upload-file").hide();
        }
    });

    $("#gFile").on("change", function (e) {
        const gphotos = this.files;
        const maxImages = 3;
        const existingImages = $("#gallery-container .gitems").length;
        const totalImages = existingImages + gphotos.length;

        // Check if adding these images would exceed the limit
        if (totalImages > maxImages) {
            showAlert(
                `You can only upload up to ${maxImages} gallery images.`,
                "danger"
            );
            this.value = ""; // Clear the file input
            return false;
        }

        $("#galUpload").removeClass("up-load");
        let imgCount = 0;

        $.each(gphotos, function (key, val) {
            imgCount++;
            const fileName = val.name;
            $("#galUpload").before(
                '<div class="item gitems">' +
                    '<img src="' +
                    URL.createObjectURL(val) +
                    '" style="width: 100px; height: 100px; object-fit: cover;" />' +
                    '<p class="file-name-overlay">' +
                    fileName +
                    "</p>" +
                    '<button type="button" class="remove-upload show" onclick="removeGalleryImage(this, \'gFile\')">Remove</button>' +
                    "</div>"
            );
        });

        // Update upload button visibility and styling
        if (totalImages >= maxImages) {
            $("#galUpload").hide();
        } else {
            $("#galUpload").show();
            if (imgCount > 2) {
                $("#galUpload").css("flex-basis", "100%");
            } else {
                $("#galUpload").css("flex-basis", "auto");
            }
        }
    });
});

// Requirements preview with file name inside the picture area
$("#requirementsFile").on("change", function (e) {
    const [file] = this.files;
    if (file) {
        $("#requirementsPreview img").attr("src", URL.createObjectURL(file));
        $("#requirementsPreview").show();
        $("#requirementsPreview .file-name-overlay").remove(); // Remove existing overlays
        $("#requirementsPreview").append(
            '<p class="file-name-overlay">' + file.name + "</p>"
        ); // Display the file name inside the picture area
        $("#requirementsPreview .remove-upload").show(); // Show the remove button
    }
});

// Function to remove gallery image
function removeGalleryImage(button, inputId) {
    $(button).parent(".gitems").remove();
    const remainingImages = $("#gallery-container .gitems").length;

    // Show upload button if we have less than 3 images
    if (remainingImages < 3) {
        $("#galUpload").show();
        // Reset file input to allow selecting new images
        $("#" + inputId).val("");
    }
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
