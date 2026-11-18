document.addEventListener("DOMContentLoaded", () => {
    const somenteDigitos = (valor) => valor.replace(/\D/g, "");

    const formatarTelefone = (valor) => {
        const digitos = somenteDigitos(valor).slice(0, 11);

        if (digitos.length <= 2) {
            return digitos.length ? `(${digitos}` : "";
        }
        if (digitos.length <= 7) {
            return `(${digitos.slice(0, 2)}) ${digitos.slice(2)}`;
        }

        return `(${digitos.slice(0, 2)}) ${digitos.slice(2, 7)}-${digitos.slice(7)}`;
    };

    const formatarCnpj = (valor) => {
        const digitos = somenteDigitos(valor).slice(0, 14);
        const partes = [
            digitos.slice(0, 2),
            digitos.slice(2, 5),
            digitos.slice(5, 8),
            digitos.slice(8, 12),
            digitos.slice(12),
        ];

        if (digitos.length <= 2) {
            return partes[0];
        }
        if (digitos.length <= 5) {
            return `${partes[0]}.${partes[1]}`;
        }
        if (digitos.length <= 8) {
            return `${partes[0]}.${partes[1]}.${partes[2]}`;
        }
        if (digitos.length <= 12) {
            return `${partes[0]}.${partes[1]}.${partes[2]}/${partes[3]}`;
        }

        return `${partes[0]}.${partes[1]}.${partes[2]}/${partes[3]}-${partes[4]}`;
    };

    document.querySelectorAll("[data-mascara]").forEach((campo) => {
        campo.addEventListener("input", () => {
            campo.value = campo.dataset.mascara === "telefone"
                ? formatarTelefone(campo.value)
                : formatarCnpj(campo.value);
        });
    });
});
