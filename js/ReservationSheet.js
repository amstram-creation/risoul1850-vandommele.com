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
    const dateSet = new Set();
    jsonData.forEach((row) => {
      if (row.c && row.c[0] && row.c[0].v) {
        const dateMatch = row.c[0].v.match(/Date\((\d+),(\d+),(\d+)\)/);
        if (dateMatch) {
          const year = dateMatch[1];
          const month = dateMatch[2];
          const day = dateMatch[3];
          const formattedDate = `${year}-${String(Number(month) + 1).padStart(
            2,
            '0'
          )}-${String(day).padStart(2, '0')}`;
          dateSet.add(formattedDate);
        }
      }
    });
    return dateSet;
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
      let formattedDate = currentDate.toISOString().split('T')[0];
      let frenchDate = `Semaine du ${formatter.format(currentDate)}`;
      let price = this.isHighSeason(formattedDate) ? '1370 €' : '830 €';

      const row = document.createElement('tr');

      let cell = document.createElement('td');
      cell.textContent = frenchDate;
      row.appendChild(cell);

      cell = document.createElement('td');
      cell.textContent = price;
      row.appendChild(cell);

      cell = document.createElement('td');
      if (reservations.has(formattedDate)) {
        cell.textContent = 'Réservé';
        cell.classList.add('booked');
      } else {
        const bookButton = document.createElement('button');
        bookButton.textContent = 'Réserver';
        bookButton.classList.add('book-now');
        bookButton.dataset.date = formattedDate;
        bookButton.addEventListener(
          'click',
          this.handleBookingClick.bind(this)
        );
        cell.appendChild(bookButton);
      }
      row.appendChild(cell);

      tbody.appendChild(row);
      currentDate.setDate(currentDate.getDate() + 7);
    }
  }

  handleBookingClick(event) {
    const date = event.target.dataset.date;
    alert(`Vous avez choisi de réserver la semaine du ${date}`);
    // Redirect to booking form or open a modal here
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
