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
            fetchNotifications();
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
        fetchNotifications();
    });
}
