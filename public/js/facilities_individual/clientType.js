function updateRoomInfo() {
    const select = document.getElementById('room_selection');
    const selectedOption = select.options[select.selectedIndex];
    const roomInfoDiv = document.getElementById('selected-room-info');
    
    if (select.value) {
        const roomName = selectedOption.getAttribute('data-room-name');
        const capacity = selectedOption.getAttribute('data-capacity');
        const sexRestriction = selectedOption.getAttribute('data-sex-restriction');
        
        document.getElementById('selected-room-name').textContent = roomName;
        document.getElementById('selected-capacity').textContent = capacity + ' person(s)';
        
        document.getElementById('hidden_room_name').value = roomName;
        document.getElementById('hidden_room_capacity').value = capacity;
        
        const sexBadge = document.getElementById('selected-sex-badge');
        const sexIcon = document.getElementById('selected-sex-icon');
        const sexText = document.getElementById('selected-sex-text');
        
        if (sexRestriction) {
            sexIcon.className = 'fa fa-' + (sexRestriction === 'male' ? 'mars' : 'venus') + ' me-1';
            sexText.textContent = sexRestriction.charAt(0).toUpperCase() + sexRestriction.slice(1);
            sexBadge.style.display = 'inline-block';
        } else {
            sexBadge.style.display = 'none';
        }
        
        roomInfoDiv.style.display = 'flex';
    } else {
        roomInfoDiv.style.display = 'none';
        document.getElementById('hidden_room_name').value = '';
        document.getElementById('hidden_room_capacity').value = '';
    }
    
    if (window.calendar) {
        window.calendar.refetchEvents();
        window.calendar.render();
    }
}

document.addEventListener('DOMContentLoaded', function() {
    const priceSelect = document.getElementById('price_id');
    if (priceSelect) {
        priceSelect.addEventListener('change', function() {
            document.getElementById('selected_price_value').value = 
                this.options[this.selectedIndex].getAttribute('data-value');
            if (window.updateTotalPrice) {
                window.updateTotalPrice();
            }
        });
    }
});