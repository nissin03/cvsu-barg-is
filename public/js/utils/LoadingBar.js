(() => {
    "use strict";

    class LoadingBar {
        constructor() {
            this.bar = null;
            this.redirecting = false;
            this.init();
        }

        init() {
            // Create loading bar if it doesn't exist
            if (!document.getElementById("global-loading-bar")) {
                this.bar = document.createElement("div");
                this.bar.id = "global-loading-bar";
                this.bar.className = "global-loading-bar";
                document.body.prepend(this.bar);

                const style = document.createElement("style");
                style.textContent = `
                    .global-loading-bar {
                        position: fixed;
                        top: 0;
                        left: 0;
                        height: 3px;
                        width: 0;
                        background-color: #18ab13;
                        z-index: 9999;
                        transition: width 0.5s ease;
                    }
                    .global-loading-bar.redirecting {
                        transition: width 0.1s ease;
                    }
                `;
                document.head.appendChild(style);
            } else {
                this.bar = document.getElementById("global-loading-bar");
            }
        }

        start() {
            this.bar.style.transition = "width 0.5s ease";
            this.bar.style.width = "80%";
            this.redirecting = false;
        }

        finish() {
            this.bar.style.transition = "width 0.1s ease";
            this.bar.classList.add("redirecting");
            this.bar.style.width = "100%";
            this.redirecting = true;
        }

        // Special method for redirects
        startRedirect() {
            this.start();
            // Add event listener for page hide (before redirect)
            window.addEventListener("pagehide", this.finish.bind(this));
        }

        // Bind to jQuery AJAX events
        bindToAjax() {
            if (window.jQuery) {
                $(document).ajaxStart(() => this.start());
                $(document).ajaxComplete(() => {
                    if (!this.redirecting) {
                        setTimeout(() => this.finish(), 100);
                    }
                });
            }
        }
    }

    // Auto-initialize
    if (!window.LoadingBar) {
        window.LoadingBar = new LoadingBar();
        document.addEventListener("DOMContentLoaded", () => {
            window.LoadingBar.bindToAjax();
        });
    }
})();
