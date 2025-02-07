export default class ReservationSheet {
  constructor(sheetId, sheetName) {
    this.extractor = new GoogleSheetExtractor(sheetId, sheetName);
  }

  

  formatDateToISO(date) {
    const year = date.getFullYear();
    const month = String(date.getMonth() + 1).padStart(2, '0');
    const day = String(date.getDate()).padStart(2, '0');
    return `${year}-${month}-${day}`;
  }

  async refreshReservations() {
    const reservations = await this.extractor.fetchReservations();

    console.log(reservations);
    //iterate on the set
    reservations.forEach((date) => {
      const cells = document.querySelectorAll(`td[data-date="${date}"]`);
      cells.forEach((cell) => {
        cell.textContent = 'Réservé';
        cell.classList.add('booked');
        cell.setAttribute('aria-label', 'Réservé');
      });
    });

    document.querySelectorAll('td[data-date]:not(.booked)').forEach((cell) => {
      const bookingLink = document.createElement('a');
      bookingLink.textContent = 'Réserver';
      bookingLink.href = `https://docs.google.com/forms/d/e/1FAIpQLSebwzkX2anwJ9q6-gAXvaNPkkjKquUhx-tEutfoHXmfUEjTFA/viewform?usp=pp_url&entry.753458274=${cell.dataset.date}`;
      bookingLink.target = '_blank';
      bookingLink.classList.add('book-now');
      bookingLink.setAttribute(
        'aria-label',
        `Réserver pour la semaine du ${cell.dataset.date}`
      );
      cell.innerHTML = '';
      cell.appendChild(bookingLink);
    });
  }

  displayReservations() {
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

    let ISODate, frenchDate, price, row, cell, loader;
    for (let i = 0; i < numWeeks; ++i) {
      ISODate = this.formatDateToISO(currentDate);
      frenchDate = `Semaine du ${formatter.format(currentDate)}`;
      price = this.isHighSeason(ISODate) ? '1370 €' : '830 €';
      const row = `
      <tr>
        <td aria-label="Semaine commençant le ${formatter.format(currentDate)}">
          ${frenchDate}
        </td>
        <td aria-label="Prix : ${price}">
          ${price}
        </td>
        <td data-date="${ISODate}">
          <a href="mailto:info@risoul1850-vandommele.com?subject=Réservation%20pour%20la%20semaine%20du%20${ISODate}" 
             target="_blank" 
             class="book-now" 
             aria-label="Réserver pour la semaine du ${ISODate}">
            Réserver
          </a>
        </td>
      </tr>
    `;
      tbody.insertAdjacentHTML('beforeend', row);
      currentDate.setDate(currentDate.getDate() + 7);
    }

    this.refreshReservations();
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

class GoogleSheetExtractor {
  constructor(sheetId, sheetName) {
    this.sheetId = sheetId;
    this.sheetName = sheetName;
    this.apiUrl = `https://docs.google.com/spreadsheets/d/${this.sheetId}/gviz/tq?tqx=out:json&sheet=${this.sheetName}`;
  }

  async fetchReservations() {
    try {
      const response = await fetch(this.apiUrl);
      const text = await response.text();
      const jsonData = this.extractJSONFromSheetsResponse(text);

      return this.extractReservationDates(jsonData.rows);
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
    const dateArray = [];
    jsonData.forEach((row) => {
      if (
        row.c &&
        row.c[row.c.length - 2] &&
        row.c[row.c.length - 2].v &&
        row.c[row.c.length - 1] &&
        row.c[row.c.length - 1].v
      ) {
        dateArray.push(row.c[row.c.length - 2].v);
      }
    });
    return dateArray;
  }
}
