export default class ReservationSheet {
  constructor(sheetId, sheetName) {
    this.extractor = new GoogleSheetExtractor(sheetId, sheetName);
    this.fresh = false;
    this.ui = new ReservationUI('#calendar-container');
    this.refreshReservations();
  }

  async refreshReservations() {
    const reservations = await this.extractor.fetchReservations();
    console.log('Reservations:', reservations);
    //iterate on the set
    if (reservations === null) {
      console.log('Unable to retrieve reservations');
      return;
    }
    reservations.forEach((date) => {
      const cell = document.querySelector(`[data-date="${date}"]:not(.booked)`);
      cell.textContent = 'Réservé';
      cell.classList.add('booked');
      cell.setAttribute('aria-label', 'Réservé');
    });

    document.querySelectorAll('[data-date]:not(.booked)').forEach((cell) => {
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
}

class ReservationUI {
  constructor(selector) {
    const container = document.querySelector(selector);
    container.innerHTML = '';

    let currentDate = new Date();
    if (currentDate.getDay() !== 6) {
      currentDate.setDate(currentDate.getDate() + (6 - currentDate.getDay()));
    }
    let _ = this.makeReservationList(currentDate, 12);
    container.appendChild(_);
  }

  makeReservationList(currentDate, numWeeks) {
    let _;
    let ISODate, frenchDate, price, listItem;
    const formatter = new Intl.DateTimeFormat('fr-FR', {
      day: 'numeric',
      month: 'long',
    });
    const fragment = document.createDocumentFragment();

    for (let i = 0; i < numWeeks; ++i) {
      ISODate = this.formatDateToISO(currentDate);
      frenchDate = `Semaine du ${formatter.format(currentDate)}`;
      price = this.isHighSeason(ISODate) ? '1370 €' : '830 €';

      listItem = document.createElement('li');
      listItem.classList.add('booking-item');

      _ = document.createElement('span');
      _.classList.add('week');
      _.setAttribute(
        'aria-label',
        `Semaine commençant le ${formatter.format(currentDate)}`
      );
      _.textContent = frenchDate;

      listItem.appendChild(_);

      _ = document.createElement('span');
      _.classList.add('price');
      _.setAttribute('aria-label', `Prix : ${price}`);
      _.textContent = price;
      listItem.appendChild(_);

      _ = document.createElement('a');
      _.href = `mailto:info@risoul1850-vandommele.com?subject=Réservation%20pour%20la%20semaine%20du%20${ISODate}`;
      _.target = '_blank';
      _.classList.add('book-now');
      _.dataset.date = ISODate;
      _.setAttribute('aria-label', `Réserver pour la semaine du ${ISODate}`);
      _.textContent = 'Réserver';
      listItem.appendChild(_);

      fragment.appendChild(listItem);

      currentDate.setDate(currentDate.getDate() + 7);
    }
    _ = document.createElement('ul');
    _.appendChild(fragment);

    return _;
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

  formatDateToISO(date) {
    const year = date.getFullYear();
    const month = String(date.getMonth() + 1).padStart(2, '0');
    const day = String(date.getDate()).padStart(2, '0');
    return `${year}-${month}-${day}`;
  }
}

class GoogleSheetExtractor {
  constructor(sheetId, sheetName) {
    this.sheetId = sheetId;
    this.sheetName = sheetName;
    this.apiUrl = `https://docs.google.com/spreadsheets/d/${this.sheetId}/gviz/tq?tqx=out:json&sheet=${this.sheetName}`;
  }

  async fetchReservations() {
    console.log('Fetching reservations...', this.apiUrl);
    let _;
    try {
      _ = await fetch(this.apiUrl);
      _ = await _.text();
      return this.extractReservationDates(_);
    } catch (error) {
      console.error('Error fetching reservations:', error);
    }
    return null;
  }

  extractReservationDates(_) {
    const regex = /\{(?:[^{}]|\{(?:[^{}]|\{[^{}]*\})*\})*\}/s;
    
    _ = _.match(regex);
    if (_ && _[0]) {
      try {
        _ = JSON.parse(_[0]);
    console.log(_.rows);

        return (_?.rows || [])
          .filter(
            (row) =>
              row?.c[0]?.f
          )
          .map((row) => row.c[0].f); // Extract the valid date values
      } catch (error) {
        console.error('Invalid JSON extracted:', error);
      }
    }
    return null;
  }
}
