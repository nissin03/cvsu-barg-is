// js/facilities_whole_building/timeSelection.js
document.addEventListener('DOMContentLoaded', function() {
    initializeTimeSelection();
});

function initializeTimeSelection() {
    const timeStartSelect = document.getElementById('time_start');
    const timeEndSelect = document.getElementById('time_end');
    
    if (timeStartSelect) {
        const userType = timeStartSelect.dataset.userType;
        const userRole = timeStartSelect.dataset.userRole;
        
        // Populate initial time options
        timeStartSelect.innerHTML = getTimeOptionsForRole(userType, userRole);
        
        // Add event listeners
        timeStartSelect.addEventListener('change', function() {
            updateEndTimeOptions(userType, userRole);
            window.updateTotalPrice();
        });
        
        timeEndSelect.addEventListener('change', window.updateTotalPrice);
        
        // Initialize end time options
        updateEndTimeOptions(userType, userRole);
    }
}

function getTimeOptionsForRole(userType, userRole) {
    let options = '';
    
    if (userType === 'ADM') {
        for(let hour = 7; hour <= 21; hour++) {
            let displayHour = hour > 12 ? hour - 12 : hour;
            if (hour === 12) displayHour = 12;
            if (hour === 0) displayHour = 12;
            const ampm = hour >= 12 ? 'PM' : 'AM';
            const value = String(hour).padStart(2, '0') + ':00';
            const display = displayHour + ':00 ' + ampm;
            options += `<option value="${value}">${display}</option>`;
        }
    } else {
        switch(userRole) {
            case 'student':
                for(let hour = 7; hour <= 15; hour++) {
                    let displayHour = hour > 12 ? hour - 12 : hour;
                    if (hour === 12) displayHour = 12;
                    const ampm = hour >= 12 ? 'PM' : 'AM';
                    const value = String(hour).padStart(2, '0') + ':00';
                    const display = displayHour + ':00 ' + ampm;
                    options += `<option value="${value}">${display}</option>`;
                }
                break;
            case 'employee':
                for(let hour = 7; hour <= 17; hour++) {
                    let displayHour = hour > 12 ? hour - 12 : hour;
                    if (hour === 12) displayHour = 12;
                    const ampm = hour >= 12 ? 'PM' : 'AM';
                    const value = String(hour).padStart(2, '0') + ':00';
                    const display = displayHour + ':00 ' + ampm;
                    options += `<option value="${value}">${display}</option>`;
                }
                break;
            case 'non-employee':
                for(let hour = 7; hour <= 21; hour++) {
                    let displayHour = hour > 12 ? hour - 12 : hour;
                    if (hour === 12) displayHour = 12;
                    if (hour === 0) displayHour = 12;
                    const ampm = hour >= 12 ? 'PM' : 'AM';
                    const value = String(hour).padStart(2, '0') + ':00';
                    const display = displayHour + ':00 ' + ampm;
                    options += `<option value="${value}">${display}</option>`;
                }
                break;
            default:
                for(let hour = 7; hour <= 15; hour++) {
                    let displayHour = hour > 12 ? hour - 12 : hour;
                    if (hour === 12) displayHour = 12;
                    const ampm = hour >= 12 ? 'PM' : 'AM';
                    const value = String(hour).padStart(2, '0') + ':00';
                    const display = displayHour + ':00 ' + ampm;
                    options += `<option value="${value}">${display}</option>`;
                }
        }
    }
    
    return options;
}

function updateEndTimeOptions(userType, userRole) {
    const timeStartSelect = document.getElementById('time_start');
    const timeEndSelect = document.getElementById('time_end');
    
    const startTime = timeStartSelect.value;
    if (!startTime) return;
    
    timeEndSelect.innerHTML = '';
    
    const startHour = parseInt(startTime.split(':')[0]);
    let maxHour;
    
    if (userType === 'ADM') {
        maxHour = 22;
    } else {
        switch(userRole) {
            case 'student':
                maxHour = 16;
                break;
            case 'employee':
                maxHour = 18;
                break;
            case 'non-employee':
                maxHour = 22;
                break;
            default:
                maxHour = 16;
        }
    }
    
    maxHour = Math.min(startHour + 8, maxHour);
    
    for (let hour = startHour + 1; hour <= maxHour; hour++) {
        const option = document.createElement('option');
        const value = (hour === 24 ? '00' : String(hour).padStart(2, '0')) + ':00';
        let displayHour = hour > 12 ? hour - 12 : hour;
        if (hour === 12) displayHour = 12;
        if (hour === 0) displayHour = 12;
        const ampm = hour >= 12 ? 'PM' : 'AM';
        option.value = value;
        option.textContent = displayHour + ':00 ' + ampm;
        timeEndSelect.appendChild(option);
    }
    
    timeEndSelect.disabled = false;
}