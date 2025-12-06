import Echo from "laravel-echo";
import Pusher from "pusher-js";
import { initNotificationManager } from "./utils/notificationManager";

window.Pusher = Pusher;

// Configure toastr
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

// Initialize notification manager when DOM is ready
document.addEventListener("DOMContentLoaded", function () {
    const userId =
        window.userId ||
        document.querySelector('meta[name="user-id"]')?.content;
    const isAdmin =
        window.isAdmin ||
        document.querySelector('meta[name="user-role"]')?.content === "ADM";

    if (userId) {
        window.Echo = new Echo({
            broadcaster: "reverb",
            key: import.meta.env.VITE_REVERB_APP_KEY,
            wsHost: import.meta.env.VITE_REVERB_HOST,
            wsPort: import.meta.env.VITE_REVERB_PORT ?? 80,
            wssPort: import.meta.env.VITE_REVERB_PORT ?? 443,
            forceTLS:
                (import.meta.env.VITE_REVERB_SCHEME ?? "https") === "https",
            enabledTransports: ["ws", "wss"],
        });

        const endpoints = isAdmin
            ? {
                  all: "/admin/notifications",
                  unread: "/admin/notifications/unread",
                  markAsRead: "/admin/notifications/mark-as-read",
                  markAllAsRead: "/admin/notifications/mark-all-as-read",
                  destroy: "/admin/notifications/destroy",
                  destroyAll: "/admin/notifications/destroy-all",
                  unreadCount: "/admin/notifications/count",
              }
            : {
                  all: "/notifications",
                  unread: "/notifications/unread",
                  markAsRead: "/notifications/mark-as-read",
                  markAllAsRead: "/notifications/mark-all-as-read",
                  destroy: "/notifications/destroy",
                  destroyAll: "/notifications/destroy-all",
                  unreadCount: "/notifications/count",
              };

        initNotificationManager({
            userId: userId,
            isAdmin: isAdmin,
            endpoints: endpoints,
            mountPointSelector: "#notification-list",
        });
    }
});
