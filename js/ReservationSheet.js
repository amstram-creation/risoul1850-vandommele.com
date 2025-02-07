export default class ReservationSheet {
  constructor(sheetId, sheetName) {
    this.sheetId = sheetId;
    this.sheetName = sheetName;
    this.apiUrl = `https://docs.google.com/spreadsheets/d/${this.sheetId}/gviz/tq?tqx=out:json&sheet=${this.sheetName}`;
  }

  async fetchReservations() {
    try {
      let response = await fetch(this.apiUrl);
      let text = await response.text();
      let jsonData = this.extractJSONFromSheetsResponse(text);
      this.displayReservations(this.extractReservationDates(jsonData.rows));
    } catch (error) {
      console.error('Error fetching reservations:', error);
    }
  }

  extractJSONFromSheetsResponse(responseText) {
    const regex = /\{(?:[^{}]|\{(?:[^{}]|\{[^{}]*\})*\})*\}/s;
    const match = responseText.match(regex);

    if (match && match[0]) {
      try {
        return JSON.parse(match[0]);
      } catch (error) {
        console.error('Invalid JSON extracted:', error);
      }
    }
    return null;
  }

  extractReservationDates(jsonData) {
    console.log(jsonData);
    const dateSet = new Set();
    jsonData.forEach((row) => {
      if (
        row.c &&
        row.c[row.c.length - 2] &&
        row.c[row.c.length - 2].v &&
        row.c[row.c.length - 1] &&
        row.c[row.c.length - 1].v
      ) {
        dateSet.add(row.c[row.c.length - 2].v);
      }
    });
    return dateSet;
  }

  formatDateToISO(date) {
    const year = date.getFullYear();
    const month = String(date.getMonth() + 1).padStart(2, '0');
    const day = String(date.getDate()).padStart(2, '0');
    return `${year}-${month}-${day}`;
  }

  displayReservations(reservations) {
    const tbody = document.querySelector('#calendar-container table tbody');
    tbody.innerHTML = '';

    let currentDate = new Date();
    if (currentDate.getDay() !== 6) {
      currentDate.setDate(currentDate.getDate() + (6 - currentDate.getDay()));
    }

    const numWeeks = 12;
    const formatter = new Intl.DateTimeFormat('fr-FR', {
      day: 'numeric',
      month: 'long',
    });

    for (let i = 0; i < numWeeks; i++) {
      let ISODate = this.formatDateToISO(currentDate);
      let frenchDate = `Semaine du ${formatter.format(currentDate)}`;
      let price = this.isHighSeason(ISODate) ? '1370 €' : '830 €';

      const row = document.createElement('tr');

      let cell = document.createElement('td');
      cell.textContent = frenchDate;
      row.appendChild(cell);

      cell = document.createElement('td');
      cell.textContent = price;
      row.appendChild(cell);

      cell = document.createElement('td');
      if (reservations.has(ISODate)) {
        cell.textContent = 'Réservé';
        cell.classList.add('booked');
      } else {
        const bookingLink = document.createElement('a');
        bookingLink.textContent = 'Réserver';
        bookingLink.href = `https://docs.google.com/forms/d/e/1FAIpQLSebwzkX2anwJ9q6-gAXvaNPkkjKquUhx-tEutfoHXmfUEjTFA/viewform?usp=pp_url&entry.753458274=${ISODate}`;
        bookingLink.target = '_blank';
        bookingLink.classList.add('book-now');
        cell.appendChild(bookingLink);
      }
      row.appendChild(cell);
      tbody.appendChild(row);
      currentDate.setDate(currentDate.getDate() + 7);
    }
  }

  handleBookingClick(event) {
    const date = event.target.dataset.date;
    alert(`Vous avez choisi de réserver la semaine du ${date}`);
  }

  isHighSeason(dateString) {
    const date = new Date(dateString);
    const month = date.getMonth() + 1;
    const day = date.getDate();

    if ((month === 12 && day >= 20) || (month === 1 && day <= 7)) return true;
    if ((month === 2 && day >= 10) || (month === 3 && day <= 10)) return true;
    if (month === 4 && day <= 15) return true;
    if (month === 7 || month === 8) return true;
    if ((month === 10 && day >= 25) || (month === 11 && day <= 5)) return true;

    return false;
  }
}
