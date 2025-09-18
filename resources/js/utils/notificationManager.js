/**
 * Unified Notification Manager
 * Works for both admin and user contexts with backward compatibility
 */
class NotificationManager {
    constructor(config) {
        this.userId = config.userId;
        this.isAdmin = config.isAdmin || false;
        this.endpoints = config.endpoints || {};
        this.mountPointSelector =
            config.mountPointSelector || "#notification-list";
        this.echo = window.Echo;
        this.axios = window.axios;

        this.setupCSRFToken();
        this.init();
    }

    setupCSRFToken() {
        const token = document.querySelector(
            'meta[name="csrf-token"]'
        )?.content;
        if (token) {
            this.axios.defaults.headers.common["X-CSRF-TOKEN"] = token;
        }
    }

    init() {
        this.setupEventListeners();
        this.setupEchoListeners();
        this.fetchNotifications();
    }

    /**
     * Fetch notifications from server
     */
    async fetchNotifications() {
        try {
            const response = await this.axios.get(
                this.endpoints.all || "/admin/notifications"
            );
            const notifications = response.data.data || response.data;
            this.updateNotificationUI(notifications);
            this.updateUnreadCount();
        } catch (error) {
            console.error("Notification fetch error:", error);
            this.showError("Unable to load notifications");
        }
    }

    /**
     * Update notification UI
     */
    updateNotificationUI(notifications) {
        const countElement = document.querySelector(".notification-count");
        if (countElement) {
            countElement.textContent = notifications.length;
        }

        const notificationList = document.querySelector(
            this.mountPointSelector
        );
        if (!notificationList) return;

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
                html += this.generateNotificationHTML(notification);
            });
            notificationList.innerHTML = html;
            this.addNotificationHandlers(notificationList);
        }
    }

    generateNotificationHTML(notification) {
        const data = notification.data || notification;
        const isRead = notification.read_at !== null;

        const title = data.title || data.name || "Notification";
        const body = data.body || data.message || "No message provided";
        const icon = data.icon || "fas fa-envelope";
        const url = data.url || null;

        return `
        <div
            class="notification-item ${isRead ? "read" : ""}"
            data-notification-id="${notification.id}"
            data-notification-data='${JSON.stringify({ url })}'
        >
            <div class="badge-icon h5">
                <i class="${icon} text-dark"></i>
            </div>
            <div class="notification-content">
                <p class="notification-text fw-bold">${title}</p>
                <p class="notification-subtext">${body}</p>
            </div>
            ${
                !isRead
                    ? `<div class="unread-indicator" title="Unread"></div>`
                    : ""
            }
        </div>
    `;
    }

    addNotificationHandlers(container) {
        container
            .querySelectorAll(".notification-item[data-notification-id]")
            .forEach((item) => {
                item.addEventListener("click", async (event) => {
                    if (event.target.closest(".remove-notification")) {
                        return;
                    }

                    const notificationId = item.getAttribute(
                        "data-notification-id"
                    );
                    const notificationData = JSON.parse(
                        item.getAttribute("data-notification-data") || "{}"
                    );

                    try {
                        this.markAsRead(notificationId, item);
                        if (notificationData.url) {
                            window.location.href = notificationData.url;
                        }
                    } catch (error) {
                        console.error(
                            "Error handling notification click:",
                            error
                        );
                    }
                });
            });

        // Remove notification
        container.querySelectorAll(".remove-notification").forEach((button) => {
            button.addEventListener("click", (event) => {
                event.stopPropagation();
                const notificationId = button.getAttribute("data-id");
                this.removeNotification(notificationId);
            });
        });
    }

    /**
     * Mark notification as read
     */
    async markAsRead(notificationId, element = null) {
        try {
            const response = await this.axios.post(
                `${
                    this.endpoints.markAsRead ||
                    "/admin/notifications/mark-as-read"
                }/${notificationId}`
            );

            if (response.data.status === "success") {
                if (element) {
                    const unreadIndicator =
                        element.querySelector(".unread-indicator");
                    if (unreadIndicator) {
                        unreadIndicator.remove();
                    }
                    element.classList.add("read");
                }
                this.updateUnreadCount();
            }
        } catch (error) {
            console.error("Error marking notification as read:", error);
            this.showError("Could not mark notification as read");
        }
    }

    /**
     * Mark all notifications as read
     */
    async markAllAsRead() {
        try {
            const response = await this.axios.post(
                this.endpoints.markAllAsRead ||
                    "/admin/notifications/mark-all-as-read"
            );

            if (response.data.status === "success") {
                this.fetchNotifications();
                this.showSuccess("All notifications marked as read");
            }
        } catch (error) {
            console.error("Error marking all notifications as read:", error);
            this.showError("Could not mark all notifications as read");
        }
    }

    /**
     * Remove notification
     */
    async removeNotification(notificationId) {
        if (!notificationId) return;

        try {
            const response = await this.axios.delete(
                `${
                    this.endpoints.destroy || "/admin/notifications/destroy"
                }/${notificationId}`
            );

            if (response.data.status === "success") {
                const notificationElement = document.querySelector(
                    `.notification-item[data-notification-id="${notificationId}"]`
                );
                if (notificationElement) {
                    notificationElement.remove();
                    this.checkEmptyState();
                }
                this.updateUnreadCount();
            }
        } catch (error) {
            console.error("Error removing notification:", error);
            this.showError("Could not remove notification");
        }
    }

    /**
     * Remove all notifications
     */
    async removeAllNotifications() {
        try {
            const response = await this.axios.delete(
                this.endpoints.destroyAll || "/admin/notifications/destroy-all"
            );

            if (response.data.status === "success") {
                this.fetchNotifications();
                this.showSuccess("All notifications removed");
            }
        } catch (error) {
            console.error("Error removing all notifications:", error);
            this.showError("Could not remove all notifications");
        }
    }

    /**
     * Update unread count
     */
    async updateUnreadCount() {
        try {
            const response = await this.axios.get(
                this.endpoints.unreadCount || "/admin/notifications/count"
            );
            const countElement = document.querySelector(".notification-count");
            if (countElement) {
                countElement.textContent = response.data.count || 0;
            }
        } catch (error) {
            console.error("Error updating unread count:", error);
        }
    }

    /**
     * Check if notification list is empty and show appropriate message
     */
    checkEmptyState() {
        const container = document.querySelector(this.mountPointSelector);
        if (container && container.children.length === 0) {
            container.innerHTML = `
                <div class="notification-item">
                    <div class="notification-content">
                        <p class="notification-text text-center">No notifications</p>
                    </div>
                </div>
            `;
        }
    }

    /**
     * Setup Echo listeners for real-time notifications
     */
    setupEchoListeners() {
        if (!this.echo) {
            console.warn(
                "Echo not available, real-time notifications disabled"
            );
            return;
        }

        // Listen to user-specific channel
        this.echo
            .private(`App.Models.User.${this.userId}`)
            .notification((notification) => {
                console.log("New notification received:", notification);
                this.handleNewNotification(notification);
            });

        // Listen to admin channel if user is admin
        if (this.isAdmin) {
            this.echo
                .private("admin-notification")
                .listen("ContactMessageReceived", (event) => {
                    console.log("New contact message:", event);
                    this.handleContactMessage(event.contactMessage);
                });

            this.echo
                .private("admin-notification")
                .listen("LowStockEvent", (event) => {
                    console.log("Low stock event:", event);
                    this.handleLowStockEvent(event.product);
                });
        }
    }

    /**
     * Handle new notification from Echo
     */
    handleNewNotification(notification) {
        // Show toast notification
        this.showToast(notification.data || notification);

        // Refresh notification list
        this.fetchNotifications();
    }

    /**
     *
     * Handle contact message event
     */
    handleContactMessage(contactMessage) {
        const notification = {
            id: Date.now(),
            data: {
                title: "New Contact Message",
                body: `New message from ${contactMessage.name}`,
                icon: "fas fa-envelope",
                url: "/admin/contacts",
            },
            created_at: new Date().toISOString(),
        };

        this.handleNewNotification(notification);
    }

    /**
     * Handle low stock event
     */
    handleLowStockEvent(product) {
        const notification = {
            id: Date.now(),
            data: {
                title: "Low Stock Alert",
                body: `${product.name} is running low on stock`,
                icon: "fas fa-exclamation-triangle",
                url: `/admin/product/edit/${product.id}`,
            },
            created_at: new Date().toISOString(),
        };

        this.handleNewNotification(notification);
    }

    handleStockUpdate(product, message) {
        const notification = {
            id: Date.now(),
            data: {
                title: "Stock Update",
                body: `Good news! ${message}`,
                icon: "fas fa-box",
                url: `/shop/product/${product.slug || product.id}`,
            },
            created_at: new Date().toISOString(),
        };
        this.handleNewNotification(notification);
    }
    /**
     * Show toast notification
     */
    showToast(notificationData) {
        if (window.toastr) {
            const title =
                notificationData.title ||
                notificationData.name ||
                "Notification";
            const body =
                notificationData.body || notificationData.message || "";

            if (
                notificationData.icon &&
                notificationData.icon.includes("exclamation")
            ) {
                window.toastr.warning(`<strong>${title}</strong>: ${body}`);
            } else if (
                notificationData.icon &&
                notificationData.icon.includes("envelope")
            ) {
                window.toastr.info(`<strong>${title}</strong>: ${body}`);
            } else {
                window.toastr.success(`<strong>${title}</strong>: ${body}`);
            }
        }
    }

    /**
     * Show success message
     */
    showSuccess(message) {
        if (window.toastr) {
            window.toastr.success(message);
        }
    }

    /**
     * Show error message
     */
    showError(message) {
        if (window.toastr) {
            window.toastr.error(message);
        } else {
            console.error(message);
        }
    }

    /**
     * Setup event listeners for UI interactions
     */
    setupEventListeners() {
        // Mark all as read button
        const markAllBtn = document.querySelector(".mark-read");
        if (markAllBtn) {
            markAllBtn.addEventListener("click", (e) => {
                e.preventDefault();
                e.stopPropagation();
                this.markAllAsRead();
            });
        }

        const removeAllBtn = document.querySelector(".remove-all");
        if (removeAllBtn) {
            removeAllBtn.addEventListener("click", (e) => {
                e.preventDefault();
                e.stopPropagation();

                const notifications = document.querySelectorAll(
                    "#notification-list .notification-item[data-notification-id], #all-notification-list .notification-item[data-notification-id]"
                );

                if (notifications.length === 0) {
                    this.showError("No notifications to remove");
                    return;
                }

                Swal.fire({
                    title: "Remove All Notifications?",
                    text: "This action cannot be undone.",
                    icon: "warning",
                    showCancelButton: true,
                    confirmButtonColor: "#d33",
                    cancelButtonColor: "#6c757d",
                    confirmButtonText: "Yes, remove all",
                    cancelButtonText: "Cancel",
                }).then((result) => {
                    if (result.isConfirmed) {
                        this.removeAllNotifications();
                        Swal.fire(
                            "Removed!",
                            "All notifications have been deleted.",
                            "success"
                        );
                    }
                });

                // if (
                //     confirm(
                //         "Are you sure you want to remove all notifications?"
                //     )
                // ) {
                //     this.removeAllNotifications();
                // }
            });
        }

        // Toggle notifications button
        const toggleBtn = document.getElementById("toggle-notifications");
        if (toggleBtn) {
            toggleBtn.addEventListener("click", (e) => {
                e.preventDefault();
                e.stopPropagation();
                this.toggleNotifications();
            });
        }

        // Prevent dropdown from closing when clicking inside
        const dropdownMenu = document.querySelector(".dropdown-menu");
        if (dropdownMenu) {
            dropdownMenu.addEventListener("click", (e) => {
                e.stopPropagation();
            });
        }
    }

    /**
     * Toggle between recent and all notifications
     */
    toggleNotifications() {
        const recentList = document.getElementById("notification-list");
        const allList = document.getElementById("all-notification-list");
        const toggleBtn = document.getElementById("toggle-notifications");

        if (!recentList || !allList || !toggleBtn) return;

        if (recentList.style.display !== "none") {
            recentList.style.display = "none";
            allList.style.display = "block";
            toggleBtn.textContent = "Show recent notifications";
            this.fetchAllNotifications();
        } else {
            recentList.style.display = "block";
            allList.style.display = "none";
            toggleBtn.textContent = "See all notifications";
        }
    }

    /**
     * Fetch all notifications for the "See all" view
     */
    async fetchAllNotifications() {
        try {
            const response = await this.axios.get(
                this.endpoints.all || "/admin/notifications"
            );
            const allList = document.getElementById("all-notification-list");

            if (allList) {
                const notifications = response.data.data || response.data;
                if (notifications.length === 0) {
                    allList.innerHTML = `
                        <div class="notification-item">
                            <div class="notification-content">
                                <p class="notification-text text-center">No notifications</p>
                            </div>
                        </div>
                    `;
                } else {
                    let html = "";
                    notifications.forEach((notification) => {
                        html += this.generateNotificationHTML(notification);
                    });
                    allList.innerHTML = html;
                    this.addNotificationHandlers(allList);
                }
            }
        } catch (error) {
            console.error("Error fetching all notifications:", error);
            const allList = document.getElementById("all-notification-list");
            if (allList) {
                allList.innerHTML = `
                    <div class="notification-item">
                        <div class="notification-content">
                            <p class="notification-text text-center">Unable to load notifications</p>
                        </div>
                    </div>
                `;
            }
        }
    }
}

/**
 * Initialize notification manager
 * @param {Object} config - Configuration object
 * @param {number} config.userId - User ID
 * @param {boolean} config.isAdmin - Whether user is admin
 * @param {Object} config.endpoints - API endpoints
 * @param {string} config.mountPointSelector - DOM selector for notification list
 */
function initNotificationManager(config) {
    return new NotificationManager(config);
}

// Export for ES6 modules
export { initNotificationManager, NotificationManager };

// Also make available globally for backward compatibility
window.initNotificationManager = initNotificationManager;
window.NotificationManager = NotificationManager;
