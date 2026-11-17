            </main>
        </div>
    </div>
    <script>
        const botaoMenu = document.querySelector(".workhub-menu-toggle");
        const menuPrincipal = document.querySelector(".workhub-sidebar");

        if (botaoMenu && menuPrincipal) {
            botaoMenu.addEventListener("click", () => {
                const menuAberto = menuPrincipal.classList.toggle("is-open");
                botaoMenu.setAttribute("aria-expanded", String(menuAberto));
            });
        }
    </script>
    <noscript>
        <style>
            @media (max-width: 767.98px) {
                .workhub-sidebar {
                    position: static;
                    transform: none;
                    width: 100%;
                }

                .workhub-page {
                    margin-left: 0;
                }

                .workhub-menu-toggle {
                    display: none;
                }
            }
        </style>
    </noscript>
</body>
</html>
