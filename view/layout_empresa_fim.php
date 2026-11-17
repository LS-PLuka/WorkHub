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
</body>
</html>
