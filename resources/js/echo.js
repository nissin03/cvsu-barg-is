// notification-manager.js

import Echo from "laravel-echo";
import Pusher from "pusher-js";
window.Pusher = Pusher;

window.Echo = new Echo({
    broadcaster: "reverb",
    key: import.meta.env.VITE_REVERB_APP_KEY,
    wsHost: import.meta.env.VITE_REVERB_HOST,
    wsPort: import.meta.env.VITE_REVERB_PORT ?? 80,
    wssPort: import.meta.env.VITE_REVERB_PORT ?? 443,
    forceTLS: (import.meta.env.VITE_REVERB_SCHEME ?? "https") === "https",
    enabledTransports: ["ws", "wss"],
});

// Toastr configuration
toastr.options = {
    closeButton: true,
    progressBar: true,
    positionClass: "toast-top-right",
    timeOut: "5000",
    extendedTimeOut: "0",
    showDuration: "300",
    hideDuration: "1000",
    preventDuplicates: false,
};

document.addEventListener("DOMContentLoaded", function () {
    // Initialize notifications UI
    fetchNotifications();

    // Setup event listeners
    setupEventListeners();

    // Set up Echo listeners
    setupEchoListeners();
});

// Fetch notifications from the database
function fetchNotifications() {
    // Fetch unread notifications to display in the dropdown
    fetch("/notifications/unread")
        .then((response) => {
            if (!response.ok) {
                throw new Error(`HTTP error! Status: ${response.status}`);
            }
            return response.json();
        })
        .then((data) => {
            updateNotificationUI(data);
        })
        .catch((error) => {
            console.error("Error fetching notifications:", error);
            // Display a fallback UI when fetch fails
            const notificationList =
                document.getElementById("notification-list");
            if (notificationList) {
                a;
                notificationList.innerHTML = `
                    <div class="notification-item">
                        <div class="notification-content">
                            <p class="notification-text text-center">Unable to load notifications</p>
                        </div>
                    </div>
                `;
            }
        });
}

// Update notification count and list in the UI
function updateNotificationUI(notifications) {
    // Update notification count
    const countElement = document.querySelector(".notification-count");
    if (countElement) {
        countElement.textContent = notifications.length;
    }

    // Update notification list
    const notificationList = document.getElementById("notification-list");
    if (notificationList) {
        if (notifications.length === 0) {
            notificationList.innerHTML = `
                <div class="notification-item">
                    <div class="notification-content">
                        <p class="notification-text text-center">No notifications</p>
                    </div>
                </div>
            `;
        } else {
            let html = "";
            // Get first 5 notifications
            const recentNotifications = notifications.slice(0, 5);

            recentNotifications.forEach((notification) => {
                html += generateNotificationHTML(notification);
            });

            notificationList.innerHTML = html;
            addNotificationHandlers(notificationList);
        }
    }
}

// Generate HTML for a notification
function generateNotificationHTML(notification) {
    const data = notification.data;
    return `
    <div class="notification-item" data-notification-id="${notification.id}">
        <div class="badge-icon h5">
            <i class="fas fa-envelope text-dark"></i>
        </div>
        <div class="notification-content">
            <p class="notification-text fw-bold">${data.name}</p>
            <p class="notification-subtext">
                ${data.message.substring(0, 30)}${
        data.message.length > 30 ? "..." : ""
    }
            </p>
        </div>
        <div class="unread-indicator"></div>
        <div class="remove-notification" data-id="${notification.id}">
            <i class="fas fa-times"></i>
        </div>
    </div>
    `;
}

// Add event listeners to notification elements
function addNotificationHandlers(container) {
    // Add click handlers for notification items
    container
        .querySelectorAll(".notification-item[data-notification-id]")
        .forEach((item) => {
            item.addEventListener("click", function () {
                const notificationId = this.getAttribute(
                    "data-notification-id"
                );
                markAsRead(notificationId);
            });
        });

    container.querySelectorAll(".remove-notification").forEach((button) => {
        button.addEventListener("click", function (event) {
            event.stopPropagation();
            const notificationId = this.getAttribute("data-id");
            removeNotification(notificationId);
        });
    });
}

// Mark a notification as read
function markAsRead(id) {
    fetch(`/notifications/mark-as-read/${id}`, {
        method: "POST",
        headers: {
            "X-CSRF-TOKEN": document
                .querySelector('meta[name="csrf-token"]')
                .getAttribute("content"),
            "Content-Type": "application/json",
        },
    })
        .then((response) => {
            if (!response.ok) {
                throw new Error(`HTTP error! Status: ${response.status}`);
            }
            return response.json();
        })
        .then((data) => {
            if (data.status === "success") {
                // Re-fetch notifications to update UI
                fetchNotifications();

                // If we have an "all notifications" view open, we need to refresh it as well
                const allNotificationList = document.getElementById(
                    "all-notification-list"
                );
                if (
                    allNotificationList &&
                    allNotificationList.style.display !== "none"
                ) {
                    fetchAllNotifications();
                }
            }
        })
        .catch((error) => {
            console.error("Error marking notification as read:", error);
            toastr.error(
                "Could not mark notification as read. Please try again."
            );
        });
}

// Mark all notifications as read
function markAllAsRead() {
    fetch("/notifications/mark-all-as-read", {
        method: "POST",
        headers: {
            "X-CSRF-TOKEN": document
                .querySelector('meta[name="csrf-token"]')
                .getAttribute("content"),
            "Content-Type": "application/json",
        },
    })
        .then((response) => {
            if (!response.ok) {
                throw new Error(`HTTP error! Status: ${response.status}`);
            }
            return response.json();
        })
        .then((data) => {
            if (data.status === "success") {
                fetchNotifications();

                // If we have an "all notifications" view open, we need to refresh it as well
                const allNotificationList = document.getElementById(
                    "all-notification-list"
                );
                if (
                    allNotificationList &&
                    allNotificationList.style.display !== "none"
                ) {
                    fetchAllNotifications();
                }

                toastr.success("All notifications marked as read");
            }
        })
        .catch((error) => {
            console.error("Error marking all notifications as read:", error);
            toastr.error(
                "Could not mark all notifications as read. Please try again."
            );
        });
}

// Remove a notification
function removeNotification(id) {
    fetch(`/notifications/destroy/${id}`, {
        method: "DELETE",
        headers: {
            "X-CSRF-TOKEN": document
                .querySelector('meta[name="csrf-token"]')
                .getAttribute("content"),
            "Content-Type": "application/json",
        },
    })
        .then((response) => {
            if (!response.ok) {
                throw new Error(`HTTP error! Status: ${response.status}`);
            }
            return response.json();
        })
        .then((data) => {
            if (data.status === "success") {
                fetchNotifications();

                // If we have an "all notifications" view open, we need to refresh it as well
                const allNotificationList = document.getElementById(
                    "all-notification-list"
                );
                if (
                    allNotificationList &&
                    allNotificationList.style.display !== "none"
                ) {
                    fetchAllNotifications();
                }
            }
        })
        .catch((error) => {
            console.error("Error removing notification:", error);
            toastr.error("Could not remove notification. Please try again.");
        });
}

// Remove all notifications
function removeAllNotifications() {
    fetch("/notifications/destroy-all", {
        method: "DELETE",
        headers: {
            "X-CSRF-TOKEN": document
                .querySelector('meta[name="csrf-token"]')
                .getAttribute("content"),
            "Content-Type": "application/json",
        },
    })
        .then((response) => {
            if (!response.ok) {
                throw new Error(`HTTP error! Status: ${response.status}`);
            }
            return response.json();
        })
        .then((data) => {
            if (data.status === "success") {
                fetchNotifications();

                // If we have an "all notifications" view open, we need to refresh it as well
                const allNotificationList = document.getElementById(
                    "all-notification-list"
                );
                if (
                    allNotificationList &&
                    allNotificationList.style.display !== "none"
                ) {
                    fetchAllNotifications();
                }

                toastr.success("All notifications removed");
            }
        })
        .catch((error) => {
            console.error("Error removing all notifications:", error);
            toastr.error(
                "Could not remove all notifications. Please try again."
            );
        });
}

// Fetch all notifications (for the "See all notifications" view)
function fetchAllNotifications() {
    fetch("/notifications")
        .then((response) => {
            if (!response.ok) {
                throw new Error(`HTTP error! Status: ${response.status}`);
            }
            return response.json();
        })
        .then((data) => {
            const allNotificationList = document.getElementById(
                "all-notification-list"
            );
            if (allNotificationList) {
                if (data.length === 0) {
                    allNotificationList.innerHTML = `
                        <div class="notification-item">
                            <div class="notification-content">
                                <p class="notification-text text-center">No notifications</p>
                            </div>
                        </div>
                    `;
                } else {
                    let html = "";

                    data.forEach((notification) => {
                        html += generateAllNotificationHTML(notification);
                    });

                    allNotificationList.innerHTML = html;
                    addNotificationHandlers(allNotificationList);
                }
            }
        })
        .catch((error) => {
            console.error("Error fetching all notifications:", error);
            const allNotificationList = document.getElementById(
                "all-notification-list"
            );
            if (allNotificationList) {
                allNotificationList.innerHTML = `
                    <div class="notification-item">
                        <div class="notification-content">
                            <p class="notification-text text-center">Unable to load notifications</p>
                        </div>
                    </div>
                `;
            }
        });
}

// Generate HTML for a notification in the "all notifications" view
function generateAllNotificationHTML(notification) {
    const data = notification.data;
    const isRead = notification.read_at !== null;

    return `
    <div class="notification-item ${
        isRead ? "read" : ""
    }" data-notification-id="${notification.id}">
        <div class="badge-icon h5">
            <i class="fas fa-envelope text-dark"></i>
        </div>
        <div class="notification-content">
            <p class="notification-text fw-bold">${data.name}</p>
            <p class="notification-subtext">
                ${data.message.substring(0, 30)}${
        data.message.length > 30 ? "..." : ""
    }
            </p>
        </div>
        ${!isRead ? '<div class="unread-indicator"></div>' : ""}
        <div class="remove-notification" data-id="${notification.id}">
            <i class="fas fa-times"></i>
        </div>
    </div>
    `;
}

// Set up UI event listeners
function setupEventListeners() {
    // Toggle between recent and all notifications
    const toggleButton = document.getElementById("toggle-notifications");
    if (toggleButton) {
        toggleButton.addEventListener("click", function (e) {
            e.preventDefault();
            e.stopPropagation();

            const recentList = document.getElementById("notification-list");
            const allList = document.getElementById("all-notification-list");

            if (recentList.style.display !== "none") {
                recentList.style.display = "none";
                allList.style.display = "block";
                this.textContent = "Show recent notifications";
                fetchAllNotifications();
            } else {
                recentList.style.display = "block";
                allList.style.display = "none";
                this.textContent = "See all notifications";
            }
        });
    }

    // Mark all as read button
    const markReadButton = document.querySelector(".mark-read");
    if (markReadButton) {
        markReadButton.addEventListener("click", function (e) {
            e.preventDefault();
            e.stopPropagation();
            markAllAsRead();
        });
    }

    // Remove all button
    const removeAllButton = document.querySelector(".remove-all");
    if (removeAllButton) {
        removeAllButton.addEventListener("click", function (e) {
            e.preventDefault();
            e.stopPropagation();

            const countElement = document.querySelector(".notification-count");
            if (countElement && parseInt(countElement.textContent) > 0) {
                if (
                    confirm(
                        "Are you sure you want to remove all notifications?"
                    )
                ) {
                    removeAllNotifications();
                }
            } else {
                toastr.info("No notifications to remove");
            }
        });
    }

    // Prevent dropdown from closing when clicking inside
    const dropdownMenu = document.querySelector(".dropdown-menu");
    if (dropdownMenu) {
        dropdownMenu.addEventListener("click", function (e) {
            e.stopPropagation();
        });
    }
}

// Set up Echo listeners for real-time events
function setupEchoListeners() {
    window.Echo.private("admin-notification").listen(
        "ContactMessageReceived",
        (e) => {
            console.log("Broadcasted and connected successfully!", e);
            const contactMessage = e.contactMessage;

            // Show toast notification
            toastr.info(
                `<strong>${
                    contactMessage.name
                }</strong>: ${contactMessage.message.substring(0, 50)}${
                    contactMessage.message.length > 50 ? "..." : ""
                }`,
                "New Contact Message",
                {
                    timeOut: "7000",
                }
            );

            // Refresh notifications
            fetchNotifications();
        }
    );

    // Keep your existing LowStockEvent listener if needed
    window.Echo.private("admin-notification").listen("LowStockEvent", (e) => {
        console.log("Low stock event received!", e);
        const productData = e.product;

        toastr.warning(
            `<strong>${productData.name}</strong> is running low on stock. Current quantity: ${productData.quantity}`,
            "Low Stock Alert",
            {
                timeOut: "7000",
            }
        );

        // Refresh notifications
        fetchNotifications();
    });
}
