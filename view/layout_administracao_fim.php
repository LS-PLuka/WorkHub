            </main>
        </div>
    </div>
    <script>
        const botaoMenuAdministracao = document.querySelector(".workhub-menu-toggle");
        const menuAdministracao = document.querySelector(".workhub-admin-sidebar");

        if (botaoMenuAdministracao && menuAdministracao) {
            botaoMenuAdministracao.addEventListener("click", () => {
                const menuAberto = menuAdministracao.classList.toggle("is-open");
                botaoMenuAdministracao.setAttribute("aria-expanded", String(menuAberto));
            });
        }
    </script>
    <noscript>
        <style>
            @media (max-width: 767.98px) {
                .workhub-admin-sidebar {
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
