/*
 * Insere máscara nos campos que precisarem com
 * o plugin jQuery Mask
 */
jQuery(document).ready(($) => {
  $("input.phone").mask("(00) 90000-0000");

  $("input.cep").mask("00000-000");
  $("input.cpf").mask("000.000.000-00");
  $("input.siape").mask("00.000.000");
  $("input.extension").mask("0000-0000");
});

/*
 * Cria um listener para preencher as informações
 * do endereço pelo CEP informado
 */
const address_cep_code_input = document.querySelector("input#address_cep_code");
if (address_cep_code_input) {
  address_cep_code_input.addEventListener("input", handleCepInput);
}

async function handleCepInput(e) {
  const value = e.target.value;

  if (isCepValid(value)) {
    changeCepCodeReqStatus("Por favor, aguarde um momento");

    const response = await getCepData(value);

    if (!response || response.erro) {
      changeCepCodeReqStatus("CEP não encontrado");

      return 1;
    }

    document.querySelector("#address_public_place").value = response.logradouro;
    document.querySelector("#address_neighborhood").value = response.bairro;
    document.querySelector("#address_city").value = response.localidade;
    document.querySelector("#address_uf").value = response.uf;

    changeCepCodeReqStatus("");
  }
}

function isCepValid(text) {
  // Regular expression to match the CEP pattern (00000-000)
  const cepPattern = /^\d{5}-\d{3}$/;

  // Test the input against the pattern
  return cepPattern.test(text);
}

async function getCepData(cep) {
  let cep_digits = cep.replace(/\D/g, "");

  const url = "https://viacep.com.br/ws/" + cep_digits + "/json/";

  try {
    const response = await fetch(url);
    if (!response.ok) {
      alert("Sinto muito, mas deu algo errado... :-/");

      console.log(response.status);

      throw new Error(`Response status: ${response.status}`);
    }

    const json = await response.json();

    return json;
  } catch (error) {
    console.error(error.message);

    return false;
  }
}

function changeCepCodeReqStatus(text) {
  document.querySelector("#cep_code_req_status").innerHTML = text;
}

/*
 * Criando listener para lidar com campos entre diferentes
 * para TAE e Docentes
 */

document.addEventListener("DOMContentLoaded", function () {
  const public_servant_bond_category = document.querySelector(
    "#public_servant_bond_category"
  );

  if (public_servant_bond_category) {
    changeFieldsByBondCategory(public_servant_bond_category.value);

    public_servant_bond_category.addEventListener("change", (e) =>
      changeFieldsByBondCategory(e.target.value)
    );
  }
});

function changeFieldsByBondCategory(bond_category) {
  // Show or hide selects based on the selected value
  const professor_fields = [
    "professor_bond_positions_row",
    "professor_bond_classes_row",
    "professor_bond_class_levels_row",
  ];

  const tae_fields = ["tae_bond_positions_row", "tae_bond_classes_row"];

  // Trata o caso da categoria escolhida ser 'Docente'
  if (bond_category.toUpperCase() === "DOCENTE") {
    // Mostra os campos referentes à categoria 'Docente'
    professor_fields.forEach((f) => {
      document.querySelector("#" + f).classList.remove("display-none");
    });
    // Habilita os campos referentes à categoria 'Docente'
    professor_fields.forEach((f) => {
      document.querySelectorAll("#" + f + " input").forEach((in_f) => {
        in_f.disabled = false;
      });
    });
    professor_fields.forEach((f) => {
      document.querySelectorAll("#" + f + " select").forEach((in_f) => {
        in_f.disabled = false;
      });
    });

    // Esconde os campos referentes à categoria 'TAE'
    tae_fields.forEach((f) => {
      document.querySelector("#" + f).classList.add("display-none");
    });
    // Desabilita os campos referentes à categoria 'TAE'
    tae_fields.forEach((f) => {
      document.querySelectorAll("#" + f + " input").forEach((in_f) => {
        in_f.disabled = true;
      });
    });
    tae_fields.forEach((f) => {
      document.querySelectorAll("#" + f + " select").forEach((in_f) => {
        in_f.disabled = true;
      });
    });
  }
  // Trata o caso da categoria escolhida ser 'TAE'
  else if (bond_category.toUpperCase() === "TAE") {
    // Esconde os campos referentes à categoria 'Docente'
    professor_fields.forEach((f) => {
      document.querySelector("#" + f).classList.add("display-none");
    });
    // Desabilita os campos referentes à categoria 'Docente'
    professor_fields.forEach((f) => {
      document.querySelectorAll("#" + f + " input").forEach((in_f) => {
        in_f.disabled = true;
      });
    });
    professor_fields.forEach((f) => {
      document.querySelectorAll("#" + f + " select").forEach((in_f) => {
        in_f.disabled = true;
      });
    });

    // Esconde os campos referentes à categoria 'TAE'
    tae_fields.forEach((f) => {
      document.querySelector("#" + f).classList.remove("display-none");
    });
    // Desabilita os campos referentes à categoria 'TAE'
    tae_fields.forEach((f) => {
      document.querySelectorAll("#" + f + " input").forEach((in_f) => {
        in_f.disabled = false;
      });
    });
    tae_fields.forEach((f) => {
      document.querySelectorAll("#" + f + " select").forEach((in_f) => {
        in_f.disabled = false;
      });
    });
  }
  // Trata o caso da categoria escolhida ser 'TERCEIRIZADO'
  else if (bond_category.toUpperCase() === "TERCEIRIZADO") {
    // Esconde os campos referentes à categoria 'Docente'
    professor_fields.forEach((f) => {
      document.querySelector("#" + f).classList.add("display-none");
    });
    // Desabilita os campos referentes à categoria 'Docente'
    professor_fields.forEach((f) => {
      document.querySelectorAll("#" + f + " input").forEach((in_f) => {
        in_f.disabled = true;
      });
    });
    professor_fields.forEach((f) => {
      document.querySelectorAll("#" + f + " select").forEach((in_f) => {
        in_f.disabled = true;
      });
    });

    // Esconde os campos referentes à categoria 'TAE'
    tae_fields.forEach((f) => {
      document.querySelector("#" + f).classList.add("display-none");
    });
    // Desabilita os campos referentes à categoria 'TAE'
    tae_fields.forEach((f) => {
      document.querySelectorAll("#" + f + " input").forEach((in_f) => {
        in_f.disabled = true;
      });
    });
    tae_fields.forEach((f) => {
      document.querySelectorAll("#" + f + " select").forEach((in_f) => {
        in_f.disabled = true;
      });
    });
  } else {
    alert(
      "Opção de 'Categoria' de vínculo de trabalho não reconhecida: " +
        bond_category.toUpperCase()
    );
  }
}

/*
 * Remove o campo 'role' do formulário de usuários
 */
document.addEventListener("DOMContentLoaded", function () {
  const roleField = document.querySelector(".user-role-wrap");
  if (roleField) {
    roleField.remove();
  }

  const roleSelect = document.querySelector("select#role");
  if (roleSelect) {
    roleSelect.disabled = true;
  }
});
