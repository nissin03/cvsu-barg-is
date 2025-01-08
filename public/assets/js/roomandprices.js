$(document).ready(function () {
    let prices = [];
    let rooms = [];


    $("#rentalType").on("change", function () {
        const rentalType = $(this).val();
        console.log("Facility Type:", rentalType);

        $("#pIndividual, #pWhole").attr("hidden", true).prop("disabled", true);

        $("#roomBox, #hideRoomBox, #dormitoryRooms, #QuantityChecked").hide();

        switch (rentalType) {
            case "individual":
                $("hideRoomBox").hide();
                $("#roomBox").show();
                $("#dormitoryRooms").show();
                $("#QuantityChecked").show();
             
                $("#pIndividual").removeAttr("hidden").prop("disabled", false);
                break;
            case "whole_place":
                $("#dormitoryRooms").hide();
                $("#roomBox").show();
                $("#hideRoomBox").show();
                $("#QuantityChecked").hide();
              
                $("#pWhole").removeAttr("hidden").prop("disabled", false);
                break;
    
            case "both":
                $("#roomBox").show();
                $("#hideRoomBox").show();
                $("#dormitoryRooms").show();
                $("#option").show();
                $("#QuantityChecked").show();
                $("#pIndividual").show();
                $("#pIndividual, #pWhole").removeAttr("hidden").prop("disabled", false);
                break;
            default:
                break;
        }
    });



    $("#rentalType").trigger("change");

    renderRoomList();

    $("#saveRoomChanges").on("click", function (event) {
        event.preventDefault();

        const roomName = $("#roomName").val();
        const roomCapacity = $("#roomCapacity").val();
        const roomSexRestriction = $("#roomSexRestriction").val();

        // Validate room data
        if (!roomName || !roomCapacity) {
            alert("Please fill in all fields");
            return;
        }
        if (isNaN(roomCapacity) || roomCapacity <= 0) {
            alert("Capacity must be a positive number.");
            return;
        }

        const newRoom = {
            room_name: roomName,
            capacity: parseInt(roomCapacity),
            sex_restriction: roomSexRestriction,
        };
        rooms.push(newRoom);
        renderRoomList();
        updateHiddenRooms();

        // Close the modal
        $("#addRoom").modal("hide");

        $("#roomName").val("");
        $("#roomCapacity").val("");
        $("#roomSexRestriction").val("");
    });

    function renderRoomList() {
        $("#roomList").empty();
        rooms.forEach((room, index) => {
            const listItem = `
                 <div class="card p-3 mb-3">
                    <div class="card-body d-flex justify-content-between align-items-center">
                        <div class="text-start">
                            <div class="d-flex justify-content-center align-items-center">
                                <h4 class="pe-2">${room.room_name}</h4>
                                <span class="badge bg-info">${room.sex_restriction}</span>
                            </div>
                            <p class="fw-bold">Capacity: <span class="badge bg-warning">${room.capacity}</span></p>
                        </div>
                        <div class="d-flex">
                    <button type="button" class="btn btn-lg btn-outline-warning me-2" onclick="editRoom(${index})"><i class="icon-pen">Edit</i></button>
                    <button type="button" class="btn btn-lg btn-outline-danger delete-btn" onclick="deleteRoom(${index})">
                    <i class="icon-trash"></i>
                </button>
                </div>
                    </div>
                </div>
            `;
            $("#roomList").append(listItem);
        });
    }

    window.editRoom = function (index) {
        const room = rooms[index];
        $("#roomName").val(room.room_name);
        $("#roomCapacity").val(room.capacity);
        $("#roomSexRestriction").val(room.sex_restriction);

        $("#saveRoomChanges")
            .off("click")
            .on("click", function () {
                rooms[index].room_name = $("#roomName").val();
                rooms[index].capacity = $("#roomCapacity").val();
                rooms[index].sex_restriction = $("#roomSexRestriction").val();
                renderRoomList();
                updateHiddenRooms();
                $("#addRoom").modal("hide");
            });

        $("#addRoom").modal("show");
    };

    window.deleteRoom = function (index) {
        rooms.splice(index, 1);
        renderRoomList();
    };

    function updateHiddenRooms() {
        const roomInput = $("#hiddenRooms");
        roomInput.empty();

        rooms.forEach((room, index) => {
            roomInput.append(
                createHiddenInputRooms(
                    `facility_attributes[${index}][room_name]`,
                    room.room_name
                )
            );
            roomInput.append(
                createHiddenInputRooms(
                    `facility_attributes[${index}][sex_restriction]`,
                    room.sex_restriction
                )
            );
            roomInput.append(
                createHiddenInputRooms(
                    `facility_attributes[${index}][capacity]`,
                    room.capacity
                )
            );
        });
    }

    // Handle Save Price Changes (Add Price)
    $("#savePriceChanges").on("click", function (event) {
        event.preventDefault();

        const name = $("#priceName").val();
        const price_type = $("#priceTypeSelect").val();
        const value = $("#value").val();
        const isBasedOnDays = $("#isBasedOnDays").prop("checked") ? 1 : 0;
        const isThereAQuantity = $("#isThereAQuantity").prop("checked") ? 1 : 0;

        // Check for required fields
        if (!name || !price_type || !value) {
            alert("Please fill in all fields");
            return;
        }

        // Validate price value
        if (isNaN(value) || parseFloat(value) <= 0) {
            alert("Price must be a valid positive number.");
            return;
        }

        const rentalType = $("#rentalType").val();

        // Add new price
        const newPrice = {
            name,
            price_type,
            value: parseFloat(value),
            is_based_on_days: isBasedOnDays,
            is_there_a_quantity: isThereAQuantity,
        };
        prices.push(newPrice);
        console.log("New Price:", newPrice);

        renderPriceList();
        updateHiddenPrices();

        // Close the price modal
        $("#addPrice").modal("hide");

        $("#priceName").val("");
        $("#priceTypeSelect").val("");
        $("#value").val("");
        $("#isBasedOnDays").prop("checked", false);
        $("#isThereAQuantity").prop("checked", false);
    });

    // Render the price list dynamically
    function renderPriceList() {
        $("#priceList").empty();

        prices.forEach((price, index) => {
            const listItem = `
              <div class="card p-3 mb-3">
                  <div class="card-body d-flex justify-content-between align-items-center">
                      <div class="text-start">
                          <h4>${price.name}</h4>
                          <p>Type: <span class="badge bg-success">${
                              price.price_type
                          }</span></p>
                          <p>Price: PHP ${price.value}</p>
                          <p>Is Based on Days?: 
                              <span class="badge ${
                                  price.is_based_on_days
                                      ? "bg-success"
                                      : "bg-danger"
                              }">
                                  ${price.is_based_on_days ? "Yes" : "No"}
                              </span>
                          </p>
                          <p>Is There A Quantity?: 
                              <span class="badge ${
                                  price.is_there_a_quantity
                                      ? "bg-success"
                                      : "bg-danger"
                              }">
                                  ${price.is_there_a_quantity ? "Yes" : "No"}
                              </span>
                          </p>
                       
                      </div>
                      <button class="btn btn-lg btn-outline-danger delete-btn" onclick="deletePrice(${index})">
                          <i class="icon-trash"></i>
                      </button>
                  </div>
              </div>
            `;
            $("#priceList").append(listItem);
        });
    }

    // Delete price
    window.deletePrice = function (index) {
        prices.splice(index, 1); // Remove price from array
        renderPriceList(); // Re-render the price list
    };

    // Update hidden inputs for prices (to be submitted with the form)
    function updateHiddenPrices() {
        const priceInput = $("#hiddenPrices");
        priceInput.empty(); // Clear existing hidden inputs

        prices.forEach((price, index) => {
            priceInput.append(
                createHiddenInput(`prices[${index}][name]`, price.name)
            );
            priceInput.append(
                createHiddenInput(
                    `prices[${index}][price_type]`,
                    price.price_type
                )
            );
            priceInput.append(
                createHiddenInput(`prices[${index}][value]`, price.value)
            );
            priceInput.append(
                createHiddenInput(
                    `prices[${index}][is_based_on_days]`,
                    price.is_based_on_days
                )
            );
            priceInput.append(
                createHiddenInput(
                    `prices[${index}][is_there_a_quantity]`,
                    price.is_there_a_quantity
                )
            );
        });
    }

    // Helper function to create hidden input for prices
    function createHiddenInput(name, value) {
        return `<input type="hidden" name="${name}" value="${value}">`;
    }

    // Helper function to create hidden input for rooms
    function createHiddenInputRooms(name, value) {
        return `<input type="hidden" name="${name}" value="${value}">`;
    }

    $("#facilityForm").on("submit", function (event) {
        event.preventDefault();

        var formData = new FormData(this);

        const facilityType = $("#rentalType").val();

        prices.forEach((price, index) => {
            formData.append(`prices[${index}][name]`, price.name);
            formData.append(`prices[${index}][price_type]`, price.price_type);
            formData.append(`prices[${index}][value]`, price.value);
            formData.append(
                `prices[${index}][is_based_on_days]`,
                price.is_based_on_days
            );
            formData.append(
                `prices[${index}][is_there_a_quantity]`,
                price.is_there_a_quantity
            );
        });

        if (facilityType === "whole_place") {
            // Only append whole_capacity
            const wholeCapacity = $("#roomCapacityWhole").val();
            formData.append("whole_capacity", wholeCapacity);
        } else {
            // Append facility attributes for individual or both
            rooms.forEach((room, index) => {
                formData.append(
                    `facility_attributes[${index}][room_name]`,
                    room.room_name
                );
                formData.append(
                    `facility_attributes[${index}][capacity]`,
                    room.capacity
                );
                formData.append(
                    `facility_attributes[${index}][sex_restriction]`,
                    room.sex_restriction
                );
            });
        }

        console.log("Form Data Before Submission:");
        for (var pair of formData.entries()) {
            console.log(pair[0] + ": " + pair[1]);
        }
        $.ajax({
            url: $(this).attr("action"),
            method: "POST",
            data: formData,
            processData: false,
            contentType: false,
            headers: {
                "X-CSRF-TOKEN": $('meta[name="csrf-token"]').attr("content"),
            },
            success: function (response) {
                console.log("Success response:", response);
                if (response.action === "create") {
                    showAlert("Facility created successfully!", "success");
                } else if (response.action === "update") {
                    showAlert("Facility updated successfully!", "success");
                }
                setTimeout(function () {
                    window.location.href = "/admin/facilities";
                }, 2000);
            },
            error: function (xhr) {
                console.log("Error:", xhr);
                if (xhr.status === 422) {
                    displayValidationErrors(xhr.responseJSON.errors);
                } else {
                    showAlert(
                        "An unexpected error occurred. Please try again.",
                        "danger"
                    );
                }
            },
        });

        console.log("Hidden form data", $("#hiddenRooms").html()); // Log hidden input fields
    });

    // Display validation errors in form
    function displayValidationErrors(errors) {
        for (const [key, messages] of Object.entries(errors)) {
            const errorContainer = $(`#${key}Error`);
            if (errorContainer.length) {
                errorContainer.html(messages[0]).show();
            }
        }
    }

    // Show custom alert
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
});
