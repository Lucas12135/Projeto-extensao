(() => {
  const toggleButton = document.getElementById("accessibility-toggle");
  const panel = document.getElementById("accessibility-panel");
  const resetButton = document.getElementById("reset-accessibility");
  const status = document.getElementById("accessibility-status");
  const options = document.querySelectorAll(".accessibility-option");
  const sliders = document.querySelectorAll(".accessibility-slider");
  const widget = toggleButton?.closest(".accessibility-widget");

  if (
    !(toggleButton instanceof HTMLButtonElement) ||
    !(panel instanceof HTMLElement) ||
    !(resetButton instanceof HTMLButtonElement) ||
    !(status instanceof HTMLElement) ||
    !(widget instanceof HTMLElement) ||
    options.length === 0 ||
    Array.from(options).some((option) => !(option instanceof HTMLButtonElement)) ||
    sliders.length !== 3 ||
    Array.from(sliders).some((slider) => !(slider instanceof HTMLInputElement))
  ) {
    throw new Error("Não foi possível inicializar as opções de acessibilidade.");
  }

  const root = document.documentElement;
  const storageKey = "reune-accessibility-settings";
  const alignmentStates = ["padrão", "à esquerda", "centralizado", "à direita"];
  const featureClasses = {
    contrast: "accessibility-high-contrast",
    links: "accessibility-highlight-links",
    "stop-animations": "accessibility-stop-animations",
    "hide-images": "accessibility-hide-images",
    "dyslexia-font": "accessibility-dyslexia-font",
    "large-cursor": "accessibility-large-cursor",
  };
  const sliderSettings = {
    textScale: {
      inputId: "accessibility-text-scale",
      outputId: "accessibility-text-scale-value",
      defaultValue: 100,
      min: 100,
      max: 200,
      format: (value) => `${value}%`,
    },
    textSpacing: {
      inputId: "accessibility-text-spacing",
      outputId: "accessibility-text-spacing-value",
      defaultValue: 0,
      min: 0,
      max: 0.3,
      format: (value) => `${value.toFixed(2).replace(".", ",")} em`,
    },
    lineHeight: {
      inputId: "accessibility-line-height",
      outputId: "accessibility-line-height-value",
      defaultValue: 1.6,
      min: 1.2,
      max: 3,
      format: (value) => value.toLocaleString("pt-BR", {
        minimumFractionDigits: 1,
        maximumFractionDigits: 1,
      }),
    },
  };
  const settings = {
    contrast: false,
    links: false,
    "stop-animations": false,
    "hide-images": false,
    "dyslexia-font": false,
    "large-cursor": false,
    textScale: 100,
    textSpacing: 0,
    lineHeight: 1.6,
    toolbarPinned: false,
    alignmentIndex: 0,
  };

  const saveSettings = () => {
    try {
      localStorage.setItem(storageKey, JSON.stringify(settings));
    } catch (error) {
      console.error("Não foi possível salvar as preferências de acessibilidade.", error);
      status.textContent = "As alterações foram aplicadas, mas não puderam ser salvas neste navegador.";
    }
  };

  try {
    const savedSettings = localStorage.getItem(storageKey);
    if (savedSettings !== null) {
      const parsedSettings = JSON.parse(savedSettings);
      if (parsedSettings && typeof parsedSettings === "object") {
        Object.keys(featureClasses).forEach((feature) => {
          if (typeof parsedSettings[feature] === "boolean") {
            settings[feature] = parsedSettings[feature];
          }
        });
        Object.entries(sliderSettings).forEach(([setting, config]) => {
          const value = parsedSettings[setting];
          if (typeof value === "number" && value >= config.min && value <= config.max) {
            settings[setting] = value;
          }
        });
        if (typeof parsedSettings["large-text"] === "boolean") {
          settings.textScale = parsedSettings["large-text"] ? 120 : 100;
        }
        if (typeof parsedSettings["text-spacing"] === "boolean") {
          settings.textSpacing = parsedSettings["text-spacing"] ? 0.12 : 0;
        }
        if (typeof parsedSettings["line-height"] === "boolean") {
          settings.lineHeight = parsedSettings["line-height"] ? 1.8 : 1.6;
        }
        if (typeof parsedSettings.toolbarPinned === "boolean") {
          settings.toolbarPinned = parsedSettings.toolbarPinned;
        }
        if (
          Number.isInteger(parsedSettings.alignmentIndex) &&
          parsedSettings.alignmentIndex >= 0 &&
          parsedSettings.alignmentIndex < alignmentStates.length
        ) {
          settings.alignmentIndex = parsedSettings.alignmentIndex;
        }
      }
    }
  } catch (error) {
    console.error("Não foi possível carregar as preferências de acessibilidade.", error);
    status.textContent = "Não foi possível carregar as preferências salvas neste navegador.";
  }

  const applySettings = () => {
    Object.entries(featureClasses).forEach(([feature, className]) => {
      root.classList.toggle(className, settings[feature]);
    });
    root.classList.toggle("accessibility-large-text", settings.textScale !== 100);
    root.classList.toggle("accessibility-text-spacing", settings.textSpacing > 0);
    root.classList.toggle("accessibility-line-height", settings.lineHeight !== 1.6);
    root.style.fontSize = settings.textScale === 100 ? "" : `${settings.textScale}%`;
    root.style.setProperty("--accessibility-letter-spacing", `${settings.textSpacing}em`);
    root.style.setProperty(
      "--accessibility-word-spacing",
      `${(settings.textSpacing * 1.33).toFixed(2)}em`,
    );
    root.style.setProperty("--accessibility-line-height", String(settings.lineHeight));

    sliders.forEach((slider) => {
      const setting = slider.dataset.setting;
      const config = sliderSettings[setting];
      const output = document.getElementById(config.outputId);
      slider.value = String(settings[setting]);
      slider.setAttribute("aria-valuetext", config.format(settings[setting]));
      if (output instanceof HTMLOutputElement) {
        output.value = config.format(settings[setting]);
      }
    });

    root.classList.remove(
      "accessibility-align-left",
      "accessibility-align-center",
      "accessibility-align-right",
    );
    if (settings.alignmentIndex > 0) {
      root.classList.add(
        `accessibility-align-${["", "left", "center", "right"][settings.alignmentIndex]}`,
      );
    }

    options.forEach((option) => {
      const feature = option.dataset.feature;
      if (feature === "pin-toolbar") {
        option.setAttribute("aria-pressed", String(settings.toolbarPinned));
      } else if (feature === "text-alignment") {
        option.setAttribute("aria-pressed", String(settings.alignmentIndex > 0));
        option.setAttribute(
          "aria-label",
          `Alinhamento de texto: ${alignmentStates[settings.alignmentIndex]}`,
        );
      } else if (feature in settings) {
        option.setAttribute("aria-pressed", String(settings[feature]));
      }
    });
  };

  const sliderBySetting = new Map(
    Array.from(sliders, (slider) => [slider.dataset.setting, slider]),
  );
  Object.entries(sliderSettings).forEach(([setting, config]) => {
    const slider = sliderBySetting.get(setting);
    if (!(slider instanceof HTMLInputElement)) {
      throw new Error(`Controle deslizante de acessibilidade ausente: ${setting}`);
    }

    slider.addEventListener("input", () => {
      settings[setting] = Number(slider.value);
      applySettings();
      saveSettings();
    });
    slider.addEventListener("change", () => {
      const labels = {
        textScale: `Tamanho do texto: ${config.format(settings[setting])}.`,
        textSpacing: `Espaçamento do texto: ${config.format(settings[setting])}.`,
        lineHeight: `Altura da linha: ${config.format(settings[setting])}.`,
      };
      status.textContent = labels[setting];
    });
  });

  applySettings();

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

  document.addEventListener("click", (event) => {
    if (!panel.hidden && event.target instanceof Node && !widget.contains(event.target)) {
      closePanel();
    }
  });

  options.forEach((option) => {
    option.addEventListener("click", () => {
      const feature = option.dataset.feature;

      if (feature === "libras") {
        const librasButton = window.VLibrasWidget?.initBtn;
        if (!(librasButton instanceof HTMLButtonElement)) {
          throw new Error("O botão do VLibras ainda não está disponível.");
        }
        librasButton.click();
        status.textContent = "Tradução em Libras aberta.";
        return;
      }

      if (feature === "pin-toolbar") {
        settings.toolbarPinned = !settings.toolbarPinned;
        option.setAttribute("aria-pressed", String(settings.toolbarPinned));
        status.textContent = settings.toolbarPinned
          ? "A barra de ferramentas permanecerá aberta."
          : "A barra de ferramentas pode ser fechada normalmente.";
        saveSettings();
        return;
      }

      if (feature === "text-alignment") {
        settings.alignmentIndex = (settings.alignmentIndex + 1) % alignmentStates.length;
        applySettings();
        status.textContent = `Alinhamento do texto: ${alignmentStates[settings.alignmentIndex]}.`;
        saveSettings();
        return;
      }

      if (feature in featureClasses) {
        settings[feature] = !settings[feature];
        applySettings();
        const action = settings[feature] ? "ativado" : "desativado";
        const messages = {
          contrast: `Alto contraste ${action}.`,
          links: `Destaque de links ${action}.`,
          "stop-animations": `Animações ${settings[feature] ? "pausadas" : "retomadas"}.`,
          "hide-images": `Imagens ${settings[feature] ? "ocultadas" : "exibidas"}.`,
          "dyslexia-font": `Fonte amigável para dislexia ${action}.`,
          "large-cursor": `Cursor ampliado ${action}.`,
        };
        status.textContent = messages[feature];
        saveSettings();
        return;
      }

      throw new Error(`Opção de acessibilidade desconhecida: ${feature}`);
    });
  });

  resetButton.addEventListener("click", () => {
    Object.keys(featureClasses).forEach((feature) => {
      settings[feature] = false;
    });
    Object.entries(sliderSettings).forEach(([setting, config]) => {
      settings[setting] = config.defaultValue;
    });
    settings.alignmentIndex = 0;
    settings.toolbarPinned = false;
    applySettings();
    status.textContent = "Todas as opções foram restauradas.";
    saveSettings();
  });

  document.addEventListener("keydown", (event) => {
    if (event.key === "Escape" && !panel.hidden) {
      closePanel();
      toggleButton.focus();
    }
  });
})();
