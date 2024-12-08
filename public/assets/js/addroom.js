function addRoomContainer() {
  const roomContainer = document.getElementById('roomContainer');

  // Create a new container for room number and capacity
  const newRoomContainer = document.createElement('div');
  newRoomContainer.classList.add('room-field-container');
  newRoomContainer.innerHTML = `
  <fieldset class="name">
      <div class="body-title mb-10">Room Number <span class="tf-color-1">*</span></div>
      <input type="text" name="name[]" placeholder="Enter Room Name" >
  </fieldset>
  <fieldset class="name">
      <div class="body-title mb-10">Room Number <span class="tf-color-1">*</span></div>
      <input type="text" name="room_number[]" placeholder="Enter room number" >
  </fieldset>

  <fieldset class="name">
      <div class="body-title mb-10">Room Capacity <span class="tf-color-1">*</span></div>
      <input type="number" name="capacity[]" placeholder="Enter room capacity">
  </fieldset>

  <button type="button" class="remove-room" onclick="removeRoomContainer(this)">Remove Room</button>
`;
  roomContainer.appendChild(newRoomContainer);
}

// Remove Room Container function
function removeRoomContainer(button) {
  const roomField = button.closest('.room-field-container');
  roomField.remove();
}


