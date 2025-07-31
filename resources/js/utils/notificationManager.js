function fetchNotifications() {
    fetch("/admin/notifications", {
        headers: {
            "X-Requested-With": "XMLHttpRequest",
            Accept: "application/json",
            "X-CSRF-TOKEN":
                document.querySelector('meta[name="csrf-token"]')?.content ||
                "",
        },
        credentials: "same-origin",
    })
        .then((response) => {
            if (response.status === 403) {
                throw new Error("Access forbidden");
            }
            if (!response.ok) {
                throw new Error(`HTTP error! Status: ${response.status}`);
            }
            return response.json();
        })
        .then((data) => {
            updateNotificationUI(data);
            const markAllBtn = document.getElementById("markAllReadBtn");
            if (markAllBtn) {
                markAllBtn.disabled = data.length === 0;
            }
        })
        .catch((error) => {
            console.error("Notification fetch error:", error);
            const notificationList =
                document.getElementById("notification-list");
            if (notificationList) {
                notificationList.innerHTML = `
                <div class="notification-item">
                    <div class="notification-content">
                        <p class="notification-text text-center">
                            ${
                                error.message.includes("forbidden")
                                    ? "Please refresh the page"
                                    : "Unable to load notifications"
                            }
                        </p>
                    </div>
                </div>
            `;
            }
        });
}

function updateNotificationUI(notifications) {
    const countElement = document.querySelector(".notification-count");
    if (countElement) {
        countElement.textContent = notifications.length;
    }
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
            const recentNotifications = notifications.slice(0, 5);
            recentNotifications.forEach((notification) => {
                html += generateNotificationHTML(notification);
            });
            notificationList.innerHTML = html;
            addNotificationHandlers(notificationList);
        }
    }
}

function addNotificationHandlers(container) {
    container
        .querySelectorAll(".notification-item[data-notification-id]")
        .forEach((item) => {
            item.addEventListener("click", function (event) {
                if (event.target.closest(".remove-notification")) {
                    return;
                }
                const notificationId = this.getAttribute(
                    "data-notification-id"
                );
                markAsReadOnly(notificationId, this);
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

function markAsReadOnly(id, notificationElement) {
    fetch(`/admin/notifications/mark-as-read/${id}`, {
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
                if (notificationElement) {
                    const unreadIndicator =
                        notificationElement.querySelector(".unread-indicator");
                    if (unreadIndicator) {
                        unreadIndicator.remove();
                    }
                    notificationElement.classList.add("read");
                }
                updateNotificationCount();
            }
        })
        .catch(() => {
            toastr.error(
                "Could not mark notification as read. Please try again."
            );
        });
}

function updateNotificationCount() {
    fetch("/admin/notifications/count")
        .then((response) => {
            if (!response.ok) {
                throw new Error(`HTTP error! Status: ${response.status}`);
            }
            return response.json();
        })
        .then((data) => {
            const countElement = document.querySelector(".notification-count");
            if (countElement) {
                countElement.textContent = data.count;
            }
        })
        .catch(() => {});
}

function markAsRead(id) {
    markAsReadOnly(id);
    fetchNotifications();

    const allNotificationList = document.getElementById(
        "all-notification-list"
    );
    if (allNotificationList && allNotificationList.style.display !== "none") {
        fetchAllNotifications();
    }
}

function markAllAsRead() {
    fetch("/admin/notifications/mark-all-as-read", {
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
        .catch(() => {
            toastr.error(
                "Could not mark all notifications as read. Please try again."
            );
        });
}
function removeNotification(id) {
    if (!id) {
        return;
    }

    fetch(`/admin/notifications/destroy/${id}`, {
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
                const notificationElement = document.querySelector(
                    `.notification-item[data-notification-id="${id}"]`
                );
                if (notificationElement) {
                    notificationElement.remove();
                    const container =
                        document.getElementById("notification-list");
                    if (container && container.children.length === 0) {
                        container.innerHTML = `
                            <div class="notification-item">
                                <div class="notification-content">
                                    <p class="notification-text text-center">No notifications</p>
                                </div>
                            </div>
                        `;
                    }
                    const allContainer = document.getElementById(
                        "all-notification-list"
                    );
                    if (allContainer && allContainer.style.display !== "none") {
                        const allNotificationElement =
                            allContainer.querySelector(
                                `.notification-item[data-notification-id="${id}"]`
                            );
                        if (allNotificationElement) {
                            allNotificationElement.remove();

                            if (allContainer.children.length === 0) {
                                allContainer.innerHTML = `
                                    <div class="notification-item">
                                        <div class="notification-content">
                                            <p class="notification-text text-center">No notifications</p>
                                        </div>
                                    </div>
                                `;
                            }
                        }
                    }
                    updateNotificationCount();
                } else {
                    fetchNotifications();

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
            }
        })
        .catch(() => {
            toastr.error("Could not remove notification. Please try again.");
        });
}
function removeAllNotifications() {
    fetch("/admin/notifications/destroy-all", {
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
        .catch(() => {
            toastr.error(
                "Could not remove all notifications. Please try again."
            );
        });
}

function fetchAllNotifications() {
    fetch("/admin/notifications", {
        headers: {
            "X-Requested-With": "XMLHttpRequest",
            Accept: "application/json",
            "X-CSRF-TOKEN":
                document.querySelector('meta[name="csrf-token"]')?.content ||
                "",
        },
        credentials: "same-origin",
    })
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
        .catch(() => {
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

function generateNotificationHTML(notification) {
    const data = notification.data;
    return `
    <div class="notification-item" data-notification-id="${notification.id}">
        <div class="badge-icon h5">
            <i class="fas fa-envelope text-dark"></i>
        </div>
        <div class="notification-content">
            <p class="notification-text fw-bold">${
                data.name || "Notification"
            }</p>
            <p class="notification-subtext">
                ${
                    data.message
                        ? data.message.substring(0, 30) +
                          (data.message.length > 30 ? "..." : "")
                        : "No message provided"
                }
            </p>
        </div>
        ${
            notification.read_at === null
                ? '<div class="unread-indicator"></div>'
                : ""
        }
        <div class="remove-notification" data-id="${notification.id}">
            <i class="fas fa-times"></i>
        </div>
    </div>
    `;
}

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
            <p class="notification-text fw-bold">${
                data.name || "Notification"
            }</p>
            <p class="notification-subtext">
                ${
                    data.message
                        ? data.message.substring(0, 30) +
                          (data.message.length > 30 ? "..." : "")
                        : "No message provided"
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

function setupEventListeners() {
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

    const markReadButton = document.querySelector(".mark-read");
    if (markReadButton) {
        markReadButton.addEventListener("click", function (e) {
            e.preventDefault();
            e.stopPropagation();
            markAllAsRead();
        });
    }
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
    const dropdownMenu = document.querySelector(".dropdown-menu");
    if (dropdownMenu) {
        dropdownMenu.addEventListener("click", function (e) {
            e.stopPropagation();
        });
    }

    document.addEventListener("click", function (event) {
        const removeButton = event.target.closest(".remove-notification");
        if (removeButton) {
            event.preventDefault();
            event.stopPropagation();

            const notificationId = removeButton.getAttribute("data-id");
            if (notificationId) {
                removeNotification(notificationId);
            }
        }
    });
}

export {
    fetchNotifications,
    setupEventListeners,
    markAsRead,
    markAllAsRead,
    removeNotification,
    removeAllNotifications,
    fetchAllNotifications,
};
