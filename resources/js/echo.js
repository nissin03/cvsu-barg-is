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

if (!localStorage.getItem("notifications")) {
    localStorage.setItem("notifications", JSON.stringify([]));
}

function removeAllNotifications() {
    localStorage.setItem("notifications", JSON.stringify([]));
    updateNotificationUI();
    toastr.success("All notifications removed");
}

document.addEventListener("DOMContentLoaded", function () {
    updateNotificationUI();

    // Mark all as read button
    const markReadButton = document.querySelector(".mark-read");
    if (markReadButton) {
        markReadButton.addEventListener("click", function (e) {
            e.preventDefault();
            e.stopPropagation();
            markAllNotificationsAsRead();
            toastr.success("All notifications marked as read");
        });
    }

    // Remove all button
    const removeAllButton = document.querySelector(".remove-all");
    if (removeAllButton) {
        removeAllButton.addEventListener("click", function (e) {
            e.preventDefault();
            e.stopPropagation();

            // Get notification count
            const notifications =
                JSON.parse(localStorage.getItem("notifications")) || [];

            if (notifications.length > 0) {
                // Ask for confirmation before removing all
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
});
function addNotification(notification) {
    let notifications = JSON.parse(localStorage.getItem("notifications")) || [];
    notification.id = Date.now();
    notification.read = false;
    notification.timestamp = new Date().toISOString();

    notifications.unshift(notification);

    localStorage.setItem("notifications", JSON.stringify(notifications));
    updateNotificationUI();
}
function removeNotification(id, event) {
    if (event) {
        event.stopPropagation();
    }

    let notifications = JSON.parse(localStorage.getItem("notifications")) || [];

    notifications = notifications.filter(
        (notification) => notification.id != id
    );
    localStorage.setItem("notifications", JSON.stringify(notifications));
    updateNotificationUI();
}

function markNotificationAsRead(id) {
    let notifications = JSON.parse(localStorage.getItem("notifications")) || [];

    notifications = notifications.map((notification) => {
        if (notification.id == id) {
            notification.read = true;
        }
        return notification;
    });

    // Save back to localStorage
    localStorage.setItem("notifications", JSON.stringify(notifications));

    // Update UI
    updateNotificationUI();
}

function markAllNotificationsAsRead() {
    let notifications = JSON.parse(localStorage.getItem("notifications")) || [];

    notifications = notifications.map((notification) => {
        notification.read = true;
        return notification;
    });
    localStorage.setItem("notifications", JSON.stringify(notifications));
    updateNotificationUI();
}
function updateNotificationUI() {
    let notifications = JSON.parse(localStorage.getItem("notifications")) || [];

    // Count unread notifications
    const unreadCount = notifications.filter(
        (notification) => !notification.read
    ).length;

    // Update count in UI
    const countElement = document.querySelector(".notification-count");
    if (countElement) {
        countElement.textContent = unreadCount;
    }

    // Update recent notification list (limited to 5)
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

    // Update all notifications list
    const allNotificationList = document.getElementById(
        "all-notification-list"
    );
    if (allNotificationList) {
        if (notifications.length === 0) {
            allNotificationList.innerHTML = `
                <div class="notification-item">
                    <div class="notification-content">
                        <p class="notification-text text-center">No notifications</p>
                    </div>
                </div>
            `;
        } else {
            let html = "";

            notifications.forEach((notification) => {
                html += generateNotificationHTML(notification);
            });

            allNotificationList.innerHTML = html;
            addNotificationHandlers(allNotificationList);
        }
    }
}

function generateNotificationHTML(notification) {
    return `
    <div class="notification-item" data-notification-id="${notification.id}">
        <div class="badge-icon h5">
            <i class="fas fa-envelope text-dark"></i>
        </div>
        <div class="notification-content">
            <p class="notification-text fw-bold">${notification.name}</p>
            <p class="notification-subtext">
                ${notification.message.substring(0, 30)}${
        notification.message.length > 30 ? "..." : ""
    }
            </p>
        </div>
        ${!notification.read ? '<div class="unread-indicator"></div>' : ""}
        <div class="remove-notification" data-id="${notification.id}">
            <i class="fas fa-times"></i>
        </div>
    </div>
    `;
}

// Helper function to add click handlers to notifications
function addNotificationHandlers(container) {
    // Add click handlers for notification items
    container
        .querySelectorAll(".notification-item[data-notification-id]")
        .forEach((item) => {
            item.addEventListener("click", function () {
                const notificationId = this.getAttribute(
                    "data-notification-id"
                );
                markNotificationAsRead(notificationId);
            });
        });

    container.querySelectorAll(".remove-notification").forEach((button) => {
        button.addEventListener("click", function (event) {
            const notificationId = this.getAttribute("data-id");
            removeNotification(notificationId, event);
        });
    });
}

document.addEventListener("DOMContentLoaded", function () {
    updateNotificationUI();

    const markReadButton = document.querySelector(".mark-read");
    if (markReadButton) {
        markReadButton.addEventListener("click", function (e) {
            e.preventDefault();
            e.stopPropagation();
            markAllNotificationsAsRead();
            toastr.success("All notifications marked as read");
        });
    }

    const removeAllButton = document.querySelector(".remove-all");
    if (removeAllButton) {
        removeAllButton.addEventListener("click", function (e) {
            e.preventDefault();
            e.stopPropagation();

            const notifications =
                JSON.parse(localStorage.getItem("notifications")) || [];

            if (notifications.length > 0) {
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
            } else {
                recentList.style.display = "block";
                allList.style.display = "none";
                this.textContent = "See all notifications";
            }
        });
    }
    document
        .querySelector(".dropdown-menu")
        .addEventListener("click", function (e) {
            e.stopPropagation();
        });
});

window.Echo.private("admin-notification").listen(
    "ContactMessageReceived",
    (e) => {
        console.log("Broadcasted and connected successfully!", e);
        const contactMessage = e.contactMessage;
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
        addNotification({
            name: contactMessage.name,
            message: contactMessage.message,
            email: contactMessage.email,
        });
    }
);

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
    addNotification({
        name: "Stock Alert",
        message: `${productData.name} is running low on stock. Current quantity: ${productData.quantity}`,
        type: "stock",
    });
});

document.addEventListener("DOMContentLoaded", function () {
    updateNotificationUI();

    const markReadButton = document.querySelector(".mark-read");
    if (markReadButton) {
        markReadButton.addEventListener("click", function (e) {
            e.preventDefault();
            e.stopPropagation();
            markAllNotificationsAsRead();
            toastr.success("All notifications marked as read");
        });
    }
});
