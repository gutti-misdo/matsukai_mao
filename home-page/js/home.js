const calendarYear = document.getElementById("calendarYear");
const calendarMonth = document.getElementById("calendarMonth");
const calendarGrid = document.getElementById("calendarGrid");
const goTodayButton = document.getElementById("goToday");
const navButtons = document.querySelectorAll(".calendar__nav");

const monthNames = ["1", "2", "3", "4", "5", "6", "7", "8", "9", "10", "11", "12"];

const sampleEvents = {
  "2026-01-01": [{ title: "元日", type: "holiday" }],
  "2026-01-13": [{ title: "成人の日", type: "holiday" }],
  "2026-01-18": [{ title: "飲み会", type: "work" }],
  "2026-01-31": [{ title: "給料日", type: "work" }],
};

const today = new Date();
const initialDate = new Date(2026, 0, 1);
let activeDate = new Date(initialDate);

const formatKey = (date) =>
  `${date.getFullYear()}-${String(date.getMonth() + 1).padStart(2, "0")}-${String(date.getDate()).padStart(
    2,
    "0"
  )}`;

const createDayCell = (date, isCurrentMonth) => {
  const wrapper = document.createElement("div");
  wrapper.className = "calendar__day";
  if (!isCurrentMonth) {
    wrapper.classList.add("calendar__day--outside");
  }

  if (
    date.getFullYear() === today.getFullYear() &&
    date.getMonth() === today.getMonth() &&
    date.getDate() === today.getDate()
  ) {
    wrapper.classList.add("calendar__day--today");
  }

  const number = document.createElement("span");
  number.className = "calendar__day-number";
  number.textContent = date.getDate();
  wrapper.appendChild(number);

  const eventsWrapper = document.createElement("div");
  eventsWrapper.className = "calendar__events";
  const key = formatKey(date);
  const events = sampleEvents[key] || [];
  events.forEach((event) => {
    const pill = document.createElement("span");
    pill.className = "calendar__event";
    if (event.type) {
      pill.classList.add(`calendar__event--${event.type}`);
    }
    pill.textContent = event.title;
    eventsWrapper.appendChild(pill);
  });
  wrapper.appendChild(eventsWrapper);

  return wrapper;
};

const renderCalendar = () => {
  const year = activeDate.getFullYear();
  const monthIndex = activeDate.getMonth();

  calendarYear.textContent = year;
  calendarMonth.textContent = monthNames[monthIndex];
  calendarGrid.innerHTML = "";

  const firstDayOfMonth = new Date(year, monthIndex, 1);
  const startDay = firstDayOfMonth.getDay();
  const daysInMonth = new Date(year, monthIndex + 1, 0).getDate();
  const daysInPrevMonth = new Date(year, monthIndex, 0).getDate();

  // Previous month days
  for (let i = startDay - 1; i >= 0; i -= 1) {
    const date = new Date(year, monthIndex - 1, daysInPrevMonth - i);
    calendarGrid.appendChild(createDayCell(date, false));
  }

  // Current month days
  for (let day = 1; day <= daysInMonth; day += 1) {
    const date = new Date(year, monthIndex, day);
    calendarGrid.appendChild(createDayCell(date, true));
  }

  // Next month filler days
  const filledCells = calendarGrid.children.length;
  const totalCells = Math.ceil(filledCells / 7) * 7;
  for (let i = filledCells; i < totalCells; i += 1) {
    const date = new Date(year, monthIndex + 1, i - filledCells + 1);
    calendarGrid.appendChild(createDayCell(date, false));
  }
};

navButtons.forEach((button) => {
  button.addEventListener("click", () => {
    const direction = button.dataset.direction;
    activeDate.setMonth(activeDate.getMonth() + (direction === "next" ? 1 : -1));
    renderCalendar();
  });
});

goTodayButton.addEventListener("click", () => {
  activeDate = new Date(today.getFullYear(), today.getMonth(), 1);
  renderCalendar();
});

renderCalendar();