(() => {
  const toggleButton = document.getElementById("accessibility-toggle");
  const panel = document.getElementById("accessibility-panel");
  const resetButton = document.getElementById("reset-accessibility");
  const status = document.getElementById("accessibility-status");
  const options = document.querySelectorAll(".accessibility-option");

  if (
    !(toggleButton instanceof HTMLButtonElement) ||
    !(panel instanceof HTMLElement) ||
    !(resetButton instanceof HTMLButtonElement) ||
    !(status instanceof HTMLElement) ||
    options.length === 0 ||
    Array.from(options).some((option) => !(option instanceof HTMLButtonElement))
  ) {
    throw new Error("Não foi possível inicializar as opções de acessibilidade.");
  }

  const root = document.documentElement;
  const alignmentStates = ["padrão", "à esquerda", "centralizado", "à direita"];
  let alignmentIndex = 0;
  let toolbarPinned = false;

  const closePanel = () => {
    panel.hidden = true;
    toggleButton.setAttribute("aria-expanded", "false");
  };

  toggleButton.addEventListener("click", () => {
    const isOpen = toggleButton.getAttribute("aria-expanded") === "true";
    panel.hidden = isOpen;
    toggleButton.setAttribute("aria-expanded", String(!isOpen));

    if (!isOpen) {
      options[0].focus();
    }
  });

  options.forEach((option) => {
    option.addEventListener("click", () => {
      const feature = option.dataset.feature;

      if (feature === "pin-toolbar") {
        toolbarPinned = !toolbarPinned;
        option.setAttribute("aria-pressed", String(toolbarPinned));
        status.textContent = toolbarPinned
          ? "A barra de ferramentas permanecerá aberta."
          : "A barra de ferramentas pode ser fechada normalmente.";
        return;
      }

      if (feature === "text-alignment") {
        alignmentIndex = (alignmentIndex + 1) % alignmentStates.length;
        root.classList.remove(
          "accessibility-align-left",
          "accessibility-align-center",
          "accessibility-align-right",
        );

        if (alignmentIndex > 0) {
          root.classList.add(`accessibility-align-${["", "left", "center", "right"][alignmentIndex]}`);
        }

        option.setAttribute("aria-pressed", String(alignmentIndex > 0));
        option.setAttribute(
          "aria-label",
          `Alinhamento de texto: ${alignmentStates[alignmentIndex]}`,
        );
        status.textContent = `Alinhamento do texto: ${alignmentStates[alignmentIndex]}.`;
        return;
      }

      const isEnabled = option.getAttribute("aria-pressed") !== "true";
      option.setAttribute("aria-pressed", String(isEnabled));

      switch (feature) {
        case "contrast":
          root.classList.toggle("accessibility-high-contrast", isEnabled);
          status.textContent = `Alto contraste ${isEnabled ? "ativado" : "desativado"}.`;
          break;
        case "links":
          root.classList.toggle("accessibility-highlight-links", isEnabled);
          status.textContent = `Destaque de links ${isEnabled ? "ativado" : "desativado"}.`;
          break;
        case "large-text":
          root.classList.toggle("accessibility-large-text", isEnabled);
          root.style.fontSize = isEnabled ? "120%" : "";
          status.textContent = `Texto maior ${isEnabled ? "ativado" : "desativado"}.`;
          break;
        case "text-spacing":
          root.classList.toggle("accessibility-text-spacing", isEnabled);
          status.textContent = `Espaçamento de texto ${isEnabled ? "ampliado" : "restaurado"}.`;
          break;
        case "stop-animations":
          root.classList.toggle("accessibility-stop-animations", isEnabled);
          status.textContent = `Animações ${isEnabled ? "pausadas" : "retomadas"}.`;
          break;
        case "hide-images":
          root.classList.toggle("accessibility-hide-images", isEnabled);
          status.textContent = `Imagens ${isEnabled ? "ocultadas" : "exibidas"}.`;
          break;
        case "dyslexia-font":
          root.classList.toggle("accessibility-dyslexia-font", isEnabled);
          status.textContent = `Fonte amigável para dislexia ${isEnabled ? "ativada" : "desativada"}.`;
          break;
        case "large-cursor":
          root.classList.toggle("accessibility-large-cursor", isEnabled);
          status.textContent = `Cursor ampliado ${isEnabled ? "ativado" : "desativado"}.`;
          break;
        case "line-height":
          root.classList.toggle("accessibility-line-height", isEnabled);
          status.textContent = `Altura da linha ${isEnabled ? "ampliada" : "restaurada"}.`;
          break;
        default:
          throw new Error(`Opção de acessibilidade desconhecida: ${feature}`);
      }
    });
  });

  resetButton.addEventListener("click", () => {
    root.classList.remove(
      "accessibility-high-contrast",
      "accessibility-highlight-links",
      "accessibility-large-text",
      "accessibility-text-spacing",
      "accessibility-stop-animations",
      "accessibility-hide-images",
      "accessibility-dyslexia-font",
      "accessibility-large-cursor",
      "accessibility-line-height",
      "accessibility-align-left",
      "accessibility-align-center",
      "accessibility-align-right",
    );
    root.style.removeProperty("font-size");
    alignmentIndex = 0;
    toolbarPinned = false;

    options.forEach((option) => option.setAttribute("aria-pressed", "false"));
    const alignmentButton = panel.querySelector('[data-feature="text-alignment"]');
    alignmentButton?.setAttribute("aria-label", "Alinhamento de texto: padrão");
    status.textContent = "Todas as opções foram restauradas.";
  });

  document.addEventListener("keydown", (event) => {
    if (event.key === "Escape" && !panel.hidden) {
      closePanel();
      toggleButton.focus();
    }
  });

})();
