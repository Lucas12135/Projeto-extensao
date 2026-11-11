(() => {
  const toggleButton = document.getElementById("accessibility-toggle");
  const panel = document.getElementById("accessibility-panel");
  const resetButton = document.getElementById("reset-accessibility");
  const status = document.getElementById("accessibility-status");
  const options = document.querySelectorAll(".accessibility-option");
  const sliders = document.querySelectorAll(".accessibility-slider");
  const tabs = document.querySelectorAll(".accessibility-tab");
  const tabPanels = document.querySelectorAll(".accessibility-tab-panel");
  const colorModeSelect = document.getElementById("accessibility-color-blind-mode");
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
    Array.from(sliders).some((slider) => !(slider instanceof HTMLInputElement)) ||
    tabs.length !== 2 ||
    Array.from(tabs).some((tab) => !(tab instanceof HTMLButtonElement)) ||
    tabPanels.length !== 2 ||
    Array.from(tabPanels).some((tabPanel) => !(tabPanel instanceof HTMLElement)) ||
    !(colorModeSelect instanceof HTMLSelectElement)
  ) {
    throw new Error("Não foi possível inicializar as opções de acessibilidade.");
  }

  const root = document.documentElement;
  const cookieKey = "reune-accessibility-settings";
  const cookieMaxAge = 60 * 60 * 24 * 365;
  const alignmentStates = ["padrão", "à esquerda", "centralizado", "à direita"];
  const colorBlindModes = {
    none: "",
    protanopia: "accessibility-color-blind-protanopia",
    protanomaly: "accessibility-color-blind-protanomaly",
    deuteranopia: "accessibility-color-blind-deuteranopia",
    deuteranomaly: "accessibility-color-blind-deuteranomaly",
    tritanopia: "accessibility-color-blind-tritanopia",
    tritanomaly: "accessibility-color-blind-tritanomaly",
    achromatopsia: "accessibility-color-blind-achromatopsia",
  };
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
    colorBlindMode: "none",
  };

  const getCookie = (name) => {
    const prefix = `${encodeURIComponent(name)}=`;
    const cookie = document.cookie
      .split(";")
      .map((part) => part.trim())
      .find((part) => part.startsWith(prefix));
    return cookie ? decodeURIComponent(cookie.slice(prefix.length)) : null;
  };

  const setCookie = (name, value) => {
    const secure = window.location.protocol === "https:" ? "; Secure" : "";
    document.cookie = `${encodeURIComponent(name)}=${encodeURIComponent(value)}; Max-Age=${cookieMaxAge}; Path=/; SameSite=Lax${secure}`;
    if (getCookie(name) !== value) {
      throw new Error("O navegador não aceitou o cookie de preferências.");
    }
  };

  const saveSettings = () => {
    try {
      setCookie(cookieKey, JSON.stringify(settings));
      return true;
    } catch (error) {
      console.error("Não foi possível salvar as preferências de acessibilidade.", error);
      status.textContent = "As alterações foram aplicadas, mas não puderam ser salvas neste navegador.";
      return false;
    }
  };

  const loadSettings = (savedSettings) => {
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
      if (
        typeof parsedSettings.colorBlindMode === "string" &&
        Object.prototype.hasOwnProperty.call(colorBlindModes, parsedSettings.colorBlindMode)
      ) {
        settings.colorBlindMode = parsedSettings.colorBlindMode;
      }
    }
  };

  try {
    const savedSettings = getCookie(cookieKey);
    if (savedSettings !== null) {
      loadSettings(savedSettings);
    } else {
      const legacySettings = localStorage.getItem(cookieKey);
      if (legacySettings !== null) {
        loadSettings(legacySettings);
        if (saveSettings()) {
          try {
            localStorage.removeItem(cookieKey);
          } catch (error) {
            console.error("Não foi possível remover as preferências antigas do navegador.", error);
          }
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
    Object.entries(colorBlindModes).forEach(([mode, className]) => {
      if (className) {
        root.classList.toggle(className, settings.colorBlindMode === mode);
      }
    });

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

    colorModeSelect.value = settings.colorBlindMode;

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

  const activateTab = (tab, moveFocus = false) => {
    tabs.forEach((currentTab) => {
      const isSelected = currentTab === tab;
      currentTab.setAttribute("aria-selected", String(isSelected));
      currentTab.tabIndex = isSelected ? 0 : -1;
      const panel = document.getElementById(currentTab.getAttribute("aria-controls"));
      panel.hidden = !isSelected;
    });
    if (moveFocus) {
      tab.focus();
    }
  };

  tabs.forEach((tab, index) => {
    tab.addEventListener("click", () => activateTab(tab));
    tab.addEventListener("keydown", (event) => {
      let nextIndex;
      if (event.key === "ArrowRight") {
        nextIndex = (index + 1) % tabs.length;
      } else if (event.key === "ArrowLeft") {
        nextIndex = (index - 1 + tabs.length) % tabs.length;
      } else if (event.key === "Home") {
        nextIndex = 0;
      } else if (event.key === "End") {
        nextIndex = tabs.length - 1;
      } else {
        return;
      }
      event.preventDefault();
      activateTab(tabs[nextIndex], true);
    });
  });

  colorModeSelect.addEventListener("change", () => {
    settings.colorBlindMode = colorModeSelect.value;
    applySettings();
    status.textContent = settings.colorBlindMode === "none"
      ? "Modo de daltonismo desativado."
      : `Modo ${colorModeSelect.selectedOptions[0].textContent} ativado.`;
    saveSettings();
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
    settings.colorBlindMode = "none";
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
