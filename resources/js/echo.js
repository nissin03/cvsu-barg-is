import Echo from "laravel-echo";
import Pusher from "pusher-js";
import {
    fetchNotifications,
    setupEventListeners,
} from "./utils/notificationManager";

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
    fetchNotifications();
    setupEventListeners();
    setupEchoListeners();
});

function setupEchoListeners() {
    window.Echo.private(`App.Models.User.${userId}`).notification(
        (notification) => {
            console.log("New Notification received:", notification);

            if (notification.hasOwnProperty("contact_id")) {
                toastr.info(
                    `<strong>${
                        notification.name
                    }</strong>: ${notification.message.substring(0, 50)}${
                        notification.message.length > 50 ? "..." : ""
                    }`,
                    "New Contact Message"
                );
            }

            if (notification.hasOwnProperty("product_id")) {
                toastr.warning(
                    `<strong>${notification.name}</strong> is running low on stock. Current quantity: ${notification.quantity}`,
                    "Low Stock Alert"
                );
            }

            fetchNotifications();
        }
    );
}
