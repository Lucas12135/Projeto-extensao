(() => {
  const editModal = document.getElementById("profile-edit-modal");
  const editTitle = document.getElementById("profile-edit-modal-title");
  const editControlContainer = document.getElementById("profile-edit-control");
  const editSaveButton = document.getElementById("profile-edit-save");

  if (
    !(editModal instanceof HTMLDialogElement) ||
    !(editTitle instanceof HTMLHeadingElement) ||
    !(editControlContainer instanceof HTMLElement) ||
    !(editSaveButton instanceof HTMLButtonElement)
  ) {
    throw new Error("Não foi possível inicializar a edição do perfil.");
  }

  let editingForm;
  let originalControl;
  let editTrigger;
  let modalControl;
  let modalAddressFields;
  let cepRequestController;
  let cepLookupTimer;
  const mensagemValidacaoEndereco = (input) => {
    if (input.validity.customError) {
      return input.validationMessage;
    }
    if (input.validity.valueMissing) {
      const label = input.parentElement.querySelector("label");
      return `Preencha o campo ${label?.textContent.trim() || "do endereço"}.`;
    }
    if (input.validity.patternMismatch) {
      return "Informe a sigla válida de um estado brasileiro.";
    }
    if (input.validity.tooLong) {
      return "O valor informado é muito longo.";
    }
    return "";
  };

  document.querySelectorAll(".profile-field").forEach((form) => {
    const control = form.querySelector('input[name="valor"], textarea[name="valor"]');
    const editButton = form.querySelector("[data-profile-edit]");
    const label = form.querySelector(".profile-field-label");

    if (
      !(control instanceof HTMLInputElement || control instanceof HTMLTextAreaElement) ||
      !(editButton instanceof HTMLButtonElement) ||
      !(label instanceof HTMLLabelElement)
    ) {
      throw new Error("Não foi possível inicializar um campo do perfil.");
    }

    editButton.addEventListener("click", () => {
      editingForm = form;
      originalControl = control;
      editTrigger = editButton;
      editTitle.textContent = `Editar ${label.textContent.trim()}`;
      editControlContainer.replaceChildren();

      const modalLabel = document.createElement("label");
      modalLabel.className = "profile-modal-label";
      modalLabel.htmlFor = "profile-edit-value";
      modalLabel.textContent = label.textContent.trim();

      modalControl = control.cloneNode(false);
      modalControl.id = "profile-edit-value";
      modalControl.removeAttribute("name");
      modalControl.disabled = false;
      modalControl.className = "profile-modal-input";
      modalControl.value = control.value;

      const fieldKey = form.querySelector('input[name="campo"]')?.value;
      let modalValidationStatus;
      if (["telefone", "cpf", "cnpj", "cep"].includes(fieldKey)) {
        modalControl.dataset.format = fieldKey;
        modalControl.dataset.validationStatus = "profile-edit-validation-status";
        modalValidationStatus = document.createElement("small");
        modalValidationStatus.id = modalControl.dataset.validationStatus;
        modalValidationStatus.className = "profile-validation-message";
        modalValidationStatus.setAttribute("aria-live", "polite");
      }
      if (fieldKey === "cep") {
        const cepWrapper = document.createElement("div");
        cepWrapper.className = "profile-cep-input-wrapper";
        cepWrapper.append(modalControl);

        const toggleAddressButton = document.createElement("button");
        toggleAddressButton.type = "button";
        toggleAddressButton.className = "profile-cep-toggle";
        toggleAddressButton.textContent = "+";
        toggleAddressButton.setAttribute("aria-expanded", "false");
        toggleAddressButton.setAttribute("aria-controls", "profile-cep-address-fields");
        toggleAddressButton.setAttribute("aria-label", "Exibir informações do endereço pelo CEP");
        cepWrapper.append(toggleAddressButton);

        modalControl.inputMode = "numeric";
        modalControl.maxLength = 9;
        modalControl.pattern = "\\d{5}-?\\d{3}";
        modalControl.title = "Informe um CEP com 8 números.";

        const addressPanel = document.createElement("div");
        addressPanel.id = "profile-cep-address-fields";
        addressPanel.className = "profile-cep-address-fields";
        addressPanel.hidden = true;

        const addressStatus = modalValidationStatus;
        addressStatus.classList.add("profile-cep-status");

        const addressDefinitions = [
          { key: "ds_logradouro", label: "Logradouro", maxLength: 150 },
          { key: "nr_endereco", label: "Número", maxLength: 20 },
          { key: "ds_complemento", label: "Complemento", maxLength: 100, optional: true },
          { key: "nm_bairro", label: "Bairro", maxLength: 100 },
          { key: "nm_cidade", label: "Cidade", maxLength: 100 },
          { key: "sg_uf", label: "UF", maxLength: 2 },
        ];
        modalAddressFields = {};
        const addressValidationStatuses = {};

        addressDefinitions.forEach(({ key, label: addressLabel, maxLength, optional }) => {
          const fieldWrapper = document.createElement("div");
          fieldWrapper.className = "profile-cep-address-field";

          const fieldLabel = document.createElement("label");
          fieldLabel.className = "profile-modal-label";
          fieldLabel.htmlFor = `profile-address-${key}`;
          fieldLabel.textContent = addressLabel;

          const addressInput = document.createElement("input");
          addressInput.id = fieldLabel.htmlFor;
          addressInput.className = "profile-modal-input";
          addressInput.type = "text";
          addressInput.maxLength = maxLength;
          addressInput.required = !optional;
          const validationStatus = document.createElement("small");
          validationStatus.id = `profile-address-${key}-status`;
          validationStatus.className = "profile-validation-message";
          validationStatus.setAttribute("aria-live", "polite");
          addressValidationStatuses[key] = validationStatus;
          if (key === "sg_uf") {
            addressInput.dataset.format = "uf";
            addressInput.dataset.validationStatus = validationStatus.id;
          }
          const hiddenInput = Array.from(form.querySelectorAll('input[type="hidden"]'))
            .find((input) => input.name === `endereco[${key}]`);
          addressInput.value = hiddenInput?.value ?? "";
          if (key === "sg_uf") {
            addressInput.pattern = "[A-Za-z]{2}";
            addressInput.title = "Informe a sigla de dois caracteres do estado.";
          }
          modalAddressFields[key] = addressInput;

          fieldWrapper.append(fieldLabel, addressInput, validationStatus);
          addressPanel.append(fieldWrapper);
        });

        const cepOriginal = modalControl.value.replace(/\D/g, "");
        let cepConsultado = null;
        let cepPrecisaConferenciaManual = false;
        const atualizarErroCep = (mensagem) => {
          modalControl.setCustomValidity(mensagem);
          modalControl.classList.toggle("documento-invalido", mensagem !== "");
          if (mensagem) {
            modalControl.setAttribute("aria-invalid", "true");
          } else {
            modalControl.removeAttribute("aria-invalid");
          }
          if (modalValidationStatus) {
            modalValidationStatus.classList.toggle("field-validation-error", mensagem !== "");
          }
        };
        const atualizarStatusConsulta = (mensagem, estado = "") => {
          addressStatus.textContent = mensagem;
          addressStatus.classList.toggle("is-error", estado === "erro");
          addressStatus.classList.toggle("is-success", estado === "sucesso");
          addressStatus.classList.toggle("is-loading", estado === "carregando");
        };
        const validarCampoEndereco = (key) => {
          const input = modalAddressFields[key];
          const status = addressValidationStatuses[key];
          const mensagem = mensagemValidacaoEndereco(input);
          status.textContent = mensagem;
          status.classList.toggle("field-validation-error", mensagem !== "");
          input.toggleAttribute("aria-invalid", mensagem !== "");
          return mensagem === "";
        };
        const exibirPainelEndereco = () => {
          if (!addressPanel.hidden) {
            return;
          }
          addressPanel.hidden = false;
          toggleAddressButton.textContent = "-";
          toggleAddressButton.setAttribute("aria-expanded", "true");
          toggleAddressButton.setAttribute("aria-label", "Ocultar informações do endereço");
        };
        const consultarCep = async () => {
          clearTimeout(cepLookupTimer);
          cepLookupTimer = undefined;
          const cep = modalControl.value.replace(/\D/g, "");
          if (cep.length !== 8 || /^0+$/.test(cep)) {
            editSaveButton.disabled = false;
            const mensagem = "Informe um CEP válido com 8 números.";
            atualizarErroCep(mensagem);
            atualizarStatusConsulta(mensagem, "erro");
            cepConsultado = null;
            return;
          }
          if (cepConsultado === cep) {
            editSaveButton.disabled = false;
            return;
          }

          cepRequestController?.abort();
          cepRequestController = new AbortController();
          const controller = cepRequestController;
          editSaveButton.disabled = true;
          atualizarStatusConsulta("Consultando o CEP...", "carregando");

          try {
            const response = await fetch(`https://viacep.com.br/ws/${cep}/json/`, {
              signal: controller.signal,
            });
            if (!response.ok) {
              throw new Error("Falha na consulta do CEP.");
            }

            const address = await response.json();
            if (
              controller.signal.aborted ||
              modalControl.value.replace(/\D/g, "") !== cep
            ) {
              return;
            }
            if (address.erro) {
              cepConsultado = null;
              cepPrecisaConferenciaManual = true;
              editSaveButton.disabled = false;
              const mensagem = "CEP não encontrado. Corrija ou informe o endereço manualmente.";
              atualizarErroCep(mensagem);
              atualizarStatusConsulta(mensagem, "erro");
              return;
            }

            modalAddressFields.ds_logradouro.value = address.logradouro ?? "";
            modalAddressFields.nm_bairro.value = address.bairro ?? "";
            modalAddressFields.nm_cidade.value = address.localidade ?? "";
            modalAddressFields.sg_uf.value = address.uf ?? "";
            modalAddressFields.sg_uf.dispatchEvent(new Event("input", { bubbles: true }));
            ["ds_logradouro", "nm_bairro", "nm_cidade", "sg_uf"].forEach(validarCampoEndereco);
            cepConsultado = cep;
            cepPrecisaConferenciaManual = false;
            editSaveButton.disabled = false;
            atualizarErroCep("");
            atualizarStatusConsulta("Endereço localizado. Você pode editar os campos antes de salvar.", "sucesso");
          } catch (error) {
            if (error instanceof DOMException && error.name === "AbortError") {
              return;
            }
            editSaveButton.disabled = false;
            cepPrecisaConferenciaManual = cep !== cepOriginal;
            const mensagem = "Não foi possível consultar o CEP. Preencha ou altere o endereço manualmente.";
            atualizarErroCep(cepPrecisaConferenciaManual ? mensagem : "");
            atualizarStatusConsulta(mensagem, "erro");
          }
        };

        modalControl.addEventListener("input", () => {
          clearTimeout(cepLookupTimer);
          cepLookupTimer = undefined;
          cepRequestController?.abort();
          cepConsultado = null;
          cepPrecisaConferenciaManual = false;
          atualizarErroCep("");
          editSaveButton.disabled = true;
          atualizarStatusConsulta("CEP alterado. A consulta será atualizada automaticamente.", "carregando");
          cepLookupTimer = setTimeout(() => {
            consultarCep();
          }, 350);
        });

        Object.entries(modalAddressFields).forEach(([key, input]) => {
          input.addEventListener("input", () => {
            validarCampoEndereco(key);
            if (cepPrecisaConferenciaManual) {
              cepPrecisaConferenciaManual = false;
              atualizarErroCep("");
              atualizarStatusConsulta("Endereço alterado manualmente. Confira os dados antes de salvar.", "sucesso");
            }
          });
          input.addEventListener("blur", () => validarCampoEndereco(key));
        });

        toggleAddressButton.addEventListener("click", () => {
          const mostrarEndereco = addressPanel.hidden;
          addressPanel.hidden = !mostrarEndereco;
          toggleAddressButton.textContent = mostrarEndereco ? "-" : "+";
          toggleAddressButton.setAttribute("aria-expanded", String(mostrarEndereco));
          toggleAddressButton.setAttribute(
            "aria-label",
            mostrarEndereco ? "Ocultar informações do endereço" : "Exibir informações do endereço pelo CEP",
          );
          if (mostrarEndereco) {
            Object.keys(modalAddressFields).forEach(validarCampoEndereco);
            consultarCep();
          }
        });

        editControlContainer.append(modalLabel, cepWrapper);
        if (modalValidationStatus) {
          editControlContainer.append(modalValidationStatus);
        }
        editControlContainer.append(addressPanel);
      } else {
        modalAddressFields = undefined;
        editControlContainer.append(modalLabel, modalControl);
        if (modalValidationStatus) {
          editControlContainer.append(modalValidationStatus);
        }
      }
      editModal.showModal();
      editControlContainer.querySelectorAll("input[data-format]").forEach((input) => {
        input.dispatchEvent(new Event("input", { bubbles: true }));
      });
      modalControl.focus();
    });
  });

  editSaveButton.addEventListener("click", () => {
    if (
      !editingForm ||
      !(originalControl instanceof HTMLInputElement || originalControl instanceof HTMLTextAreaElement) ||
      !(modalControl instanceof HTMLInputElement || modalControl instanceof HTMLTextAreaElement)
    ) {
      throw new Error("Não foi possível salvar a alteração do perfil.");
    }

    if (!modalControl.reportValidity()) {
      return;
    }

    if (modalAddressFields) {
      for (const [key, addressInput] of Object.entries(modalAddressFields)) {
        const status = document.getElementById(`profile-address-${key}-status`);
        const mensagem = mensagemValidacaoEndereco(addressInput);
        const campoValido = mensagem === "";
        if (status) {
          status.textContent = mensagem;
          status.classList.toggle("field-validation-error", !campoValido);
        }
        addressInput.toggleAttribute("aria-invalid", !campoValido);
        if (!campoValido || !addressInput.reportValidity()) {
          const addressPanel = editControlContainer.querySelector(".profile-cep-address-fields");
          const toggleAddressButton = editControlContainer.querySelector(".profile-cep-toggle");
          if (addressPanel instanceof HTMLElement) {
            addressPanel.hidden = false;
          }
          if (toggleAddressButton instanceof HTMLButtonElement) {
            toggleAddressButton.textContent = "-";
            toggleAddressButton.setAttribute("aria-expanded", "true");
            toggleAddressButton.setAttribute("aria-label", "Ocultar informações do endereço");
          }
          addressInput.focus();
          return;
        }
        const hiddenInput = Array.from(editingForm.querySelectorAll('input[type="hidden"]'))
          .find((input) => input.name === `endereco[${key}]`);
        if (!(hiddenInput instanceof HTMLInputElement)) {
          throw new Error("Não foi possível salvar as informações do endereço.");
        }
        hiddenInput.value = addressInput.value.trim();
      }

      const localizacao = [
        modalAddressFields.ds_logradouro.value,
        modalAddressFields.nr_endereco.value,
        modalAddressFields.ds_complemento.value,
        modalAddressFields.nm_bairro.value,
        modalAddressFields.nm_cidade.value,
        modalAddressFields.sg_uf.value,
        modalControl.value,
      ].map((parte) => parte.trim()).filter((parte) => parte !== "").join(", ");
      if (Array.from(localizacao).length > 255) {
        const status = editControlContainer.querySelector(".profile-cep-status");
        const addressPanel = editControlContainer.querySelector(".profile-cep-address-fields");
        const toggleAddressButton = editControlContainer.querySelector(".profile-cep-toggle");
        if (addressPanel instanceof HTMLElement) {
          addressPanel.hidden = false;
        }
        if (toggleAddressButton instanceof HTMLButtonElement) {
          toggleAddressButton.textContent = "-";
          toggleAddressButton.setAttribute("aria-expanded", "true");
          toggleAddressButton.setAttribute("aria-label", "Ocultar informações do endereço");
        }
        if (status) {
          status.textContent = "O endereço completo deve ter até 255 caracteres.";
          status.classList.add("is-error");
        }
        return;
      }
    }

    originalControl.value = modalControl.value;
    originalControl.disabled = false;
    originalControl.dispatchEvent(new Event("input", { bubbles: true }));
    const formToSubmit = editingForm;
    editModal.close();
    formToSubmit.requestSubmit();
  });

  const confirmationForms = [
    { formClass: "profile-logout-form", modalId: "profile-logout-modal" },
    { formClass: "profile-delete-form", modalId: "profile-delete-modal" },
  ];

  confirmationForms.forEach(({ formClass, modalId }) => {
    const form = document.querySelector(`.${formClass}`);
    const modal = document.getElementById(modalId);
    const confirmButton = modal?.querySelector(`[data-confirm-form="${formClass}"]`);
    const confirmationInput = formClass === "profile-delete-form"
      ? document.getElementById("profile-delete-confirmation")
      : null;

    if (
      !(form instanceof HTMLFormElement) ||
      !(modal instanceof HTMLDialogElement) ||
      !(confirmButton instanceof HTMLButtonElement) ||
      (confirmationInput !== null && !(confirmationInput instanceof HTMLInputElement))
    ) {
      throw new Error(`Não foi possível inicializar a confirmação: ${formClass}.`);
    }

    if (confirmationInput instanceof HTMLInputElement) {
      confirmationInput.addEventListener("input", () => {
        confirmButton.disabled = confirmationInput.value !== "CONFIRMAR";
      });
    }

    let confirmed = false;
    form.addEventListener("submit", (event) => {
      if (confirmed) {
        confirmed = false;
        return;
      }
      event.preventDefault();
      if (modal.open) {
        return;
      }
      if (confirmationInput instanceof HTMLInputElement) {
        confirmationInput.value = "";
        confirmButton.disabled = true;
      }
      modal.showModal();
      if (confirmationInput instanceof HTMLInputElement) {
        confirmationInput.focus();
      }
    });

    confirmButton.addEventListener("click", () => {
      if (
        confirmationInput instanceof HTMLInputElement &&
        confirmationInput.value !== "CONFIRMAR"
      ) {
        return;
      }
      confirmed = true;
      modal.close();
      form.requestSubmit();
    });
  });

  document.querySelectorAll("[data-profile-modal-close]").forEach((button) => {
    button.addEventListener("click", () => {
      const modal = button.closest("dialog");
      if (modal instanceof HTMLDialogElement) {
        modal.close();
      }
    });
  });

  document.querySelectorAll("dialog.profile-modal").forEach((modal) => {
    modal.addEventListener("click", (event) => {
      if (event.target === modal && modal instanceof HTMLDialogElement) {
        modal.close();
      }
    });
  });

  editModal.addEventListener("close", () => {
    clearTimeout(cepLookupTimer);
    cepLookupTimer = undefined;
    cepRequestController?.abort();
    cepRequestController = undefined;
    editSaveButton.disabled = false;
    editControlContainer.replaceChildren();
    if (editTrigger instanceof HTMLButtonElement) {
      editTrigger.focus();
    }
    editingForm = undefined;
    originalControl = undefined;
    editTrigger = undefined;
    modalControl = undefined;
    modalAddressFields = undefined;
  });
})();
