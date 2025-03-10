// $(document).ready(function () {
// let prices = [];
// let rooms = [];

// const $rentalType = $("#rentalType");
// const $roomBox = $("#roomBox");
// const $hideRoomBox = $("#hideRoomBox");
// const $dormitoryRooms = $("#dormitoryRooms");
// const $quantityChecked = $("#QuantityChecked");
// const $pIndividual = $("#pIndividual");
// const $pWhole = $("#pWhole");
// // const $roomList = $("#roomList");
// // const $hiddenRooms = $("#hiddenRooms");
// // const $multipleRoomsTable = $("#multipleRoomsTable tbody");
// // const $addRoomModal = $("#addRoom");


// $rentalType.on("change", function () {
//     handleRentalTypeChange($(this).val());
// });

// function handleRentalTypeChange(rentalType) {
//     console.log("Facility Type:", rentalType);

//     // Reset visibility
//     $pIndividual.add($pWhole).attr("hidden", true).prop("disabled", true);
//     $roomBox.add($hideRoomBox).add($dormitoryRooms).add($quantityChecked).hide();

//     switch (rentalType) {
//         case "individual":
//             $roomBox.show();
//             $dormitoryRooms.show();
//             $quantityChecked.show();
//             $pIndividual.removeAttr("hidden").prop("disabled", false);
//             break;

//         case "whole_place":
//             $roomBox.show();
//             $hideRoomBox.show();
//             $pWhole.removeAttr("hidden").prop("disabled", false);
//             break;

//         case "both":
//             $roomBox.show();
//             $hideRoomBox.show();
//             $dormitoryRooms.show();
//             $quantityChecked.show();
//             $pIndividual.add($pWhole).removeAttr("hidden").prop("disabled", false);
//             break;
//     }
// }

// // Trigger initial change event
// $rentalType.trigger("change");

// renderRoomList();

// Save single room entry
// $("#saveRoomChanges").on("click", function (event) {
//     event.preventDefault();
//     saveRoom();
// });

// function saveRoom() {
//     const roomName = $("#roomName").val().trim();
//     const roomCapacity = $("#roomCapacity").val().trim();
//     const roomSexRestriction = $("#roomSexRestriction").val();

//     if (!roomName || !roomCapacity || isNaN(roomCapacity) || roomCapacity <= 0) {
//         alert("Please enter a valid room name and positive capacity.");
//         return;
//     }

//     rooms.push({
//         room_name: roomName,
//         capacity: parseInt(roomCapacity),
//         sex_restriction: roomSexRestriction,
//     });

//     updateUI();
// }

// function renderRoomList() {
//     $roomList.empty();
//     rooms.forEach((room, index) => {
//         $roomList.append(createRoomCard(room, index));
//     });
// }

// function createRoomCard(room, index) {
//     return `
//         <div class="card p-3 mb-3">
//             <div class="card-body d-flex justify-content-between align-items-center">
//                 <div>
//                     <h4 class="pe-2">${room.room_name}</h4>
//                     <span class="badge bg-info">${room.sex_restriction}</span>
//                     <p class="fw-bold">Capacity: <span class="badge bg-warning">${room.capacity}</span></p>
//                 </div>
//                 <div class="d-flex">
//                     <button type="button" class="btn btn-lg btn-outline-warning me-2 edit-room" data-index="${index}">
//                         <i class="icon-pen">Edit</i>
//                     </button>
//                     <button type="button" class="btn btn-lg btn-outline-danger delete-room" data-index="${index}">
//                         <i class="icon-trash"></i>
//                     </button>
//                 </div>
//             </div>
//         </div>`;
// }

// function updateUI() {
//     renderRoomList();
//     updateHiddenRooms();
//     $addRoomModal.modal("hide");
//     clearRoomForm();
// }

// function clearRoomForm() {
//     $("#roomName, #roomCapacity").val("");
//     $("#roomSexRestriction").val("");
// }

// $roomList.on("click", ".edit-room", function () {
//     const index = $(this).data("index");
//     editRoom(index);
// });

// function editRoom(index) {
//     const room = rooms[index];
//     $("#roomName").val(room.room_name);
//     $("#roomCapacity").val(room.capacity);
//     $("#roomSexRestriction").val(room.sex_restriction);

//     $("#saveRoomChanges").off("click").on("click", function () {
//         rooms[index] = {
//             room_name: $("#roomName").val(),
//             capacity: parseInt($("#roomCapacity").val()),
//             sex_restriction: $("#roomSexRestriction").val(),
//         };
//         updateUI();
//     });

//     $addRoomModal.modal("show");
// }

// $roomList.on("click", ".delete-room", function () {
//     const index = $(this).data("index");
//     rooms.splice(index, 1);
//     updateUI();
// });

// function updateHiddenRooms() {
//     $hiddenRooms.empty();
//     rooms.forEach((room, index) => {
//         $hiddenRooms.append(createHiddenInput(`facility_attributes[${index}][room_name]`, room.room_name));
//         $hiddenRooms.append(createHiddenInput(`facility_attributes[${index}][sex_restriction]`, room.sex_restriction));
//         $hiddenRooms.append(createHiddenInput(`facility_attributes[${index}][capacity]`, room.capacity));
//     });
// }

// function createHiddenInput(name, value) {
//     return `<input type="hidden" name="${name}" value="${value}">`;
// }

// // Add multiple rooms
// $("#addMultipleRoomsRowBtn").on("click", function (e) {
//     e.preventDefault();
//     $multipleRoomsTable.append(createRoomRow());
// });

// function createRoomRow() {
//     return `
//         <tr>
//             <td><input type="text" class="form-control room-name" placeholder="Enter room name"></td>
//             <td><input type="number" class="form-control room-capacity" min="1" placeholder="Enter capacity"></td>
//             <td>
//                 <select class="form-control room-sex-restriction">
//                     <option value="">Choose Sex Restriction...</option>
//                     <option value="male">Male</option>
//                     <option value="female">Female</option>
//                 </select>
//             </td>
//             <td><button type="button" class="btn btn-danger removeRowBtn">Remove</button></td>
//         </tr>`;
// }

// // Remove multiple room row
// $multipleRoomsTable.on("click", ".removeRowBtn", function () {
//     $(this).closest("tr").remove();
// });

// // Save multiple rooms
// $("#saveMultipleRoomsBtn").on("click", function (e) {
//     e.preventDefault();
//     saveMultipleRooms();
// });

// function saveMultipleRooms() {
//     let valid = true;
//     let newRooms = [];

//     $multipleRoomsTable.find("tr").each(function () {
//         const roomName = $(this).find(".room-name").val().trim();
//         const roomCapacity = $(this).find(".room-capacity").val().trim();
//         const roomSexRestriction = $(this).find(".room-sex-restriction").val();

//         if (!roomName || !roomCapacity || isNaN(roomCapacity) || parseInt(roomCapacity) <= 0) {
//             valid = false;
//             return false;
//         }

//         newRooms.push({ room_name: roomName, capacity: parseInt(roomCapacity), sex_restriction: roomSexRestriction });
//     });

//     if (!valid) {
//         alert("Please ensure all rows have valid inputs.");
//         return;
//     }

//     rooms = rooms.concat(newRooms);
//     updateUI();
//     $multipleRoomsTable.empty();
//     $("#addMultipleRoomsModal").modal("hide");
// }

// const $priceList = $("#priceList");
// const $hiddenPrices = $("#hiddenPrices");
// const $addPriceModal = $("#addPrice");

// // Handle Save Price Changes (Add Price)
// $("#savePriceChanges").on("click", function (event) {
//     event.preventDefault();
//     savePrice();
// });

// function savePrice() {
//     const name = $("#priceName").val().trim();
//     const priceType = $("#priceTypeSelect").val();
//     const value = $("#value").val().trim();
//     const isBasedOnDays = $("#isBasedOnDays").prop("checked") ? 1 : 0;
//     const isThereAQuantity = $("#isThereAQuantity").prop("checked") ? 1 : 0;

//     if (!name || !priceType || !value) {
//         alert("Please fill in all fields.");
//         return;
//     }

//     if (isNaN(value) || parseFloat(value) <= 0) {
//         alert("Price must be a valid positive number.");
//         return;
//     }

//     prices.push({
//         name,
//         price_type: priceType,
//         value: parseFloat(value),
//         is_based_on_days: isBasedOnDays,
//         is_there_a_quantity: isThereAQuantity,
//     });

//     updatePriceUI();
// }

// function renderPriceList() {
//     $priceList.empty();
//     prices.forEach((price, index) => {
//         $priceList.append(createPriceCard(price, index));
//     });
// }

// function createPriceCard(price, index) {
//     return `
//         <div class="card p-3 mb-3">
//             <div class="card-body d-flex justify-content-between align-items-center">
//                 <div>
//                     <h4>${price.name}</h4>
//                     <p>Type: <span class="badge bg-success">${price.price_type}</span></p>
//                     <p>Price: PHP ${price.value}</p>
//                     <p>Is Based on Days?: 
//                         <span class="badge ${price.is_based_on_days ? "bg-success" : "bg-danger"}">
//                             ${price.is_based_on_days ? "Yes" : "No"}
//                         </span>
//                     </p>
//                     <p>Is There A Quantity?: 
//                         <span class="badge ${price.is_there_a_quantity ? "bg-success" : "bg-danger"}">
//                             ${price.is_there_a_quantity ? "Yes" : "No"}
//                         </span>
//                     </p>
//                 </div>
//                 <button class="btn btn-lg btn-outline-danger delete-price" data-index="${index}">
//                     <i class="icon-trash"></i>
//                 </button>
//             </div>
//         </div>`;
// }

// function updatePriceUI() {
//     renderPriceList();
//     updateHiddenPrices();
//     $addPriceModal.modal("hide");
//     clearPriceForm();
// }

// function clearPriceForm() {
//     $("#priceName, #value").val("");
//     $("#priceTypeSelect").val("");
//     $("#isBasedOnDays, #isThereAQuantity").prop("checked", false);
// }

// // Delete price (using event delegation)
// $priceList.on("click", ".delete-price", function () {
//     const index = $(this).data("index");
//     prices.splice(index, 1);
//     updatePriceUI();
// });

// function updateHiddenPrices() {
//     $hiddenPrices.empty();
//     prices.forEach((price, index) => {
//         $hiddenPrices.append(createHiddenInput(`prices[${index}][name]`, price.name));
//         $hiddenPrices.append(createHiddenInput(`prices[${index}][price_type]`, price.price_type));
//         $hiddenPrices.append(createHiddenInput(`prices[${index}][value]`, price.value));
//         $hiddenPrices.append(createHiddenInput(`prices[${index}][is_based_on_days]`, price.is_based_on_days));
//         $hiddenPrices.append(createHiddenInput(`prices[${index}][is_there_a_quantity]`, price.is_there_a_quantity));
//     });
// }


$("#facilityForm").on("submit", function (event) {
    event.preventDefault();

    var formData = new FormData(this);

    const facilityType = $("#rentalType").val();

    // prices.forEach((price, index) => {
    //     formData.append(`prices[${index}][name]`, price.name);
    //     formData.append(`prices[${index}][price_type]`, price.price_type);
    //     formData.append(`prices[${index}][value]`, price.value);
    //     formData.append(
    //         `prices[${index}][is_based_on_days]`,
    //         price.is_based_on_days
    //     );
    //     formData.append(
    //         `prices[${index}][is_there_a_quantity]`,
    //         price.is_there_a_quantity
    //     );
    // });

    // if (facilityType === "whole_place") {
    //     // Only append whole_capacity
    //     const wholeCapacity = $("#roomCapacityWhole").val();
    //     formData.append("whole_capacity", wholeCapacity);
    // } else {
    //     // Append facility attributes for individual or both
    //     rooms.forEach((room, index) => {
    //         formData.append(
    //             `facility_attributes[${index}][room_name]`,
    //             room.room_name
    //         );
    //         formData.append(
    //             `facility_attributes[${index}][capacity]`,
    //             room.capacity
    //         );
    //         formData.append(
    //             `facility_attributes[${index}][sex_restriction]`,
    //             room.sex_restriction
    //         );
    //     });
    // }

    window.price.forEach((price, index) => {
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
        const wholeCapacity = $("#roomCapacityWhole").val();
        formData.append("whole_capacity", wholeCapacity);
    } else {
        window.rooms.forEach((room, index) => {
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

    console.log("Hidden form data", $("#hiddenRooms").html());
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
// });
