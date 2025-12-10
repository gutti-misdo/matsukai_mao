const payrollList = document.getElementById("payrollList");
const payrollMessage = document.getElementById("payrollMessage");
const payrollMonthInput = document.getElementById("payrollMonth");
const infoBanner = document.getElementById("payrollInfo");

const setPayrollMessage = (text, type = "info") => {
  if (!payrollMessage) return;
  payrollMessage.textContent = text;
  payrollMessage.className = `payroll__message payroll__message--${type}`;
  payrollMessage.style.display = text ? "block" : "none";
};

const formatCurrency = (value) => Number(value || 0).toLocaleString();

const renderSummaries = (summaries) => {
  if (!payrollList) return;
  payrollList.innerHTML = "";

  if (!summaries || summaries.length === 0) {
    const empty = document.createElement("div");
    empty.className = "payroll__empty";
    empty.textContent = "この月のアルバイト予定はありません。";
    payrollList.appendChild(empty);
    return;
  }

  summaries.forEach((summary) => {
    const card = document.createElement("div");
    card.className = "payroll-card";

    const title = document.createElement("h2");
    title.className = "payroll-card__title";
    title.textContent = summary.shop_name;
    card.appendChild(title);

    const meta = document.createElement("div");
    meta.className = "payroll-card__meta";
    const hours = document.createElement("span");
    hours.textContent = `勤務時間: ${summary.total_hours}時間`;
    const shifts = document.createElement("span");
    shifts.textContent = `勤務回数: ${summary.shift_count}回`;
    const wage = document.createElement("span");
    wage.textContent = `時給: ${formatCurrency(summary.hourly_wage)}円`;
    meta.append(hours, shifts, wage);

    if (summary.travel_expenses !== null && summary.travel_total !== null) {
      const travel = document.createElement("span");
      travel.textContent = `交通費: ${formatCurrency(summary.travel_expenses)}円/回 (計 ${formatCurrency(summary.travel_total)}円)`;
      meta.appendChild(travel);
    }

    card.appendChild(meta);

    const payRow = document.createElement("div");
    payRow.className = "payroll-card__pay";
    const label = document.createElement("span");
    label.className = "payroll-card__label";
    label.textContent = "時間×時給 合計";
    const amount = document.createElement("span");
    amount.className = "payroll-card__amount";
    amount.textContent = `${formatCurrency(summary.total_pay)}円`;
    payRow.append(label, amount);

    card.appendChild(payRow);
    payrollList.appendChild(card);
  });
};

const fetchPayroll = async (month) => {
  if (!month) return;
  setPayrollMessage("計算中です...", "info");
  try {
    const response = await fetch(`./api/payroll.php?month=${month}`);
    if (!response.ok) {
      throw new Error("給与計算の取得に失敗しました");
    }
    const data = await response.json();
    renderSummaries(data.summaries || []);
    setPayrollMessage("", "info");
  } catch (error) {
    console.error(error);
    renderSummaries([]);
    setPayrollMessage("給与計算の取得に失敗しました。時間をおいて再度お試しください。", "error");
  }
};

if (payrollMonthInput) {
  payrollMonthInput.addEventListener("change", (event) => {
    fetchPayroll(event.target.value);
  });

  if (payrollMonthInput.value) {
    fetchPayroll(payrollMonthInput.value);
  }
}

if (infoBanner) {
  infoBanner.setAttribute("role", "status");
  infoBanner.setAttribute("aria-live", "polite");
}
