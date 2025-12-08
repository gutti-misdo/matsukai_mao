const calendarYear = document.getElementById("calendarYear");
const calendarMonth = document.getElementById("calendarMonth");
const calendarGrid = document.getElementById("calendarGrid");
const goTodayButton = document.getElementById("goToday");
const navButtons = document.querySelectorAll(".calendar__nav");
const eventForm = document.getElementById("eventForm");
const eventTitleInput = document.getElementById("eventTitle");
const eventDateInput = document.getElementById("eventDate");
const eventMessage = document.getElementById("eventMessage");
const openAddFormButton = document.getElementById("openAddForm");

const monthNames = ["1", "2", "3", "4", "5", "6", "7", "8", "9", "10", "11", "12"];

let holidayEvents = {};
let userEvents = {};

const formatKey = (date) =>
  `${date.getFullYear()}-${String(date.getMonth() + 1).padStart(2, "0")}-${String(date.getDate()).padStart(
    2,
    "0"
  )}`;

const getVernalEquinox = (year) =>
  Math.floor(20.8431 + 0.242194 * (year - 1980) - Math.floor((year - 1980) / 4));
const getAutumnEquinox = (year) =>
  Math.floor(23.2488 + 0.242194 * (year - 1980) - Math.floor((year - 1980) / 4));

const createDate = (year, monthIndex, day) => new Date(year, monthIndex, day);
const getNthMonday = (year, monthIndex, n) => {
  const firstDay = createDate(year, monthIndex, 1).getDay();
  const firstMonday = ((1 - firstDay + 7) % 7) + 1;
  return createDate(year, monthIndex, firstMonday + 7 * (n - 1));
};

const generateBaseHolidays = (year) => {
  const holidays = [
    { date: createDate(year, 0, 1), name: "元日" },
    { date: getNthMonday(year, 0, 2), name: "成人の日" },
    { date: createDate(year, 1, 11), name: "建国記念の日" },
    { date: createDate(year, 1, 23), name: "天皇誕生日" },
    { date: createDate(year, 2, getVernalEquinox(year)), name: "春分の日" },
    { date: createDate(year, 3, 29), name: "昭和の日" },
    { date: createDate(year, 4, 3), name: "憲法記念日" },
    { date: createDate(year, 4, 4), name: "みどりの日" },
    { date: createDate(year, 4, 5), name: "こどもの日" },
    { date: getNthMonday(year, 6, 3), name: "海の日" },
    { date: createDate(year, 7, 11), name: "山の日" },
    { date: getNthMonday(year, 8, 3), name: "敬老の日" },
    { date: createDate(year, 8, getAutumnEquinox(year)), name: "秋分の日" },
    { date: getNthMonday(year, 9, 2), name: "スポーツの日" },
    { date: createDate(year, 10, 3), name: "文化の日" },
    { date: createDate(year, 10, 23), name: "勤労感謝の日" },
  ];
  return holidays;
};

const addSubstituteHolidays = (holidays) => {
  const holidayDates = new Set(holidays.map((holiday) => formatKey(holiday.date)));
  holidays.forEach((holiday) => {
    if (holiday.date.getDay() !== 0) return;

    let substituteDate = new Date(holiday.date);
    do {
      substituteDate.setDate(substituteDate.getDate() + 1);
    } while (holidayDates.has(formatKey(substituteDate)));

    holidayDates.add(formatKey(substituteDate));
    holidays.push({ date: substituteDate, name: "振替休日" });
  });
};

const addCitizenHolidays = (holidays) => {
  const holidayDates = new Set(holidays.map((holiday) => formatKey(holiday.date)));
  const sorted = holidays.sort((a, b) => a.date - b.date);

  for (let i = 1; i < sorted.length; i += 1) {
    const prev = sorted[i - 1];
    const current = sorted[i];
    const diff = (current.date - prev.date) / (1000 * 60 * 60 * 24);

    if (diff === 2) {
      const middleDate = new Date(prev.date);
      middleDate.setDate(prev.date.getDate() + 1);
      const middleKey = formatKey(middleDate);
      if (!holidayDates.has(middleKey)) {
        holidayDates.add(middleKey);
        holidays.push({ date: middleDate, name: "国民の休日" });
      }
    }
  }
};

const buildLocalHolidayEvents = (year) => {
  const holidays = generateBaseHolidays(year);
  addSubstituteHolidays(holidays);
  addCitizenHolidays(holidays);

  return holidays.reduce((acc, holiday) => {
    acc[formatKey(holiday.date)] = holiday.name;
    return acc;
  }, {});
};

const fetchHolidayEvents = async (year) => {
  if (Object.keys(holidayEvents).length > 0) {
    return;
  }

  try {
    const response = await fetch("https://holidays-jp.github.io/api/v1/date.json");
    if (!response.ok) {
      throw new Error("祝日情報の取得に失敗しました");
    }

    const data = await response.json();
    holidayEvents = data;
  } catch (error) {
    console.error(error);
    const years = [year - 1, year, year + 1];
    holidayEvents = years.reduce(
      (acc, currentYear) => ({ ...acc, ...buildLocalHolidayEvents(currentYear) }),
      {}
    );
  }
};

const fetchUserEvents = async (year, monthIndex) => {
  const monthKey = `${year}-${String(monthIndex + 1).padStart(2, "0")}`;
  try {
    const response = await fetch(`./api/events.php?month=${monthKey}`);
    if (!response.ok) {
      throw new Error("予定の取得に失敗しました");
    }
    const data = await response.json();
    userEvents = (data.events || []).reduce((acc, event) => {
      if (!acc[event.event_date]) {
        acc[event.event_date] = [];
      }
      acc[event.event_date].push({ title: event.title, eventId: event.event_id });
      return acc;
    }, {});
  } catch (error) {
    console.error(error);
    setMessage("予定の取得に失敗しました。時間をおいて再度お試しください。", "error");
    userEvents = {};
  }
};

const today = new Date();
const initialDate = new Date(today.getFullYear(), today.getMonth(), 1);
let activeDate = new Date(initialDate);

const setMessage = (text, type = "info") => {
  if (!eventMessage) return;
  eventMessage.textContent = text;
  eventMessage.className = `planner__message planner__message--${type}`;
};

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
  const events = [];
  if (holidayEvents[key]) {
    events.push({ title: holidayEvents[key], type: "holiday" });
  }
  if (userEvents[key]) {
    userEvents[key].forEach((event) => {
      events.push({ title: event.title, type: "user" });
    });
  }

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

const renderCalendar = async () => {
  const year = activeDate.getFullYear();
  await fetchHolidayEvents(year);
  await fetchUserEvents(year, activeDate.getMonth());

  const monthIndex = activeDate.getMonth();

  calendarYear.textContent = year;
  calendarMonth.textContent = monthNames[monthIndex];
  calendarGrid.innerHTML = "";

  const firstDayOfMonth = new Date(year, monthIndex, 1);
  const startDay = firstDayOfMonth.getDay();
  const daysInMonth = new Date(year, monthIndex + 1, 0).getDate();
  const daysInPrevMonth = new Date(year, monthIndex, 0).getDate();

  for (let i = startDay - 1; i >= 0; i -= 1) {
    const date = new Date(year, monthIndex - 1, daysInPrevMonth - i);
    calendarGrid.appendChild(createDayCell(date, false));
  }

  for (let day = 1; day <= daysInMonth; day += 1) {
    const date = new Date(year, monthIndex, day);
    calendarGrid.appendChild(createDayCell(date, true));
  }

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

if (eventForm && eventTitleInput && eventDateInput) {
  eventForm.addEventListener("submit", async (event) => {
    event.preventDefault();
    const title = eventTitleInput.value.trim();
    const eventDate = eventDateInput.value;

    if (!title || !eventDate) {
      setMessage("タイトルと日付を入力してください。", "error");
      return;
    }

    try {
      const response = await fetch("./api/events.php", {
        method: "POST",
        headers: { "Content-Type": "application/json" },
        body: JSON.stringify({ title, event_date: eventDate }),
      });

      if (!response.ok) {
        throw new Error("保存に失敗しました");
      }

      const data = await response.json();
      if (!userEvents[eventDate]) {
        userEvents[eventDate] = [];
      }
      userEvents[eventDate].push({ title: data.title, eventId: data.event_id });
      setMessage("予定を追加しました。", "success");
      eventTitleInput.value = "";
      renderCalendar();
    } catch (error) {
      console.error(error);
      setMessage("予定の保存に失敗しました。入力内容を確認してください。", "error");
    }
  });
}

if (openAddFormButton && eventForm && eventTitleInput) {
  openAddFormButton.addEventListener("click", () => {
    eventTitleInput.focus();
    eventForm.scrollIntoView({ behavior: "smooth", block: "center" });
  });
}

renderCalendar();
