export default class ReservationSheet {
  constructor(selector, sheetId, sheetName) {
    this.container = document.querySelector(selector);
    this.url = `https://docs.google.com/spreadsheets/d/${sheetId}/gviz/tq?tqx=out:json&sheet=${sheetName}`;
    this.render();
  }

  render() {
    let _;
    _ = this.nextSaturday();
    _ = this.makeReservationList(_, 12);
    this.container.innerHTML = '';
    this.container.appendChild(_);

    return this;
  }

  async fetch() {
    const regex = /\{(?:[^{}]|\{(?:[^{}]|\{[^{}]*\})*\})*\}/s;

    let _;

    try {
      console.log('Fetching reservations...', this.url);
      _ = await fetch(this.url);
      _ = await _.text();
      _ = _.match(regex);
      if (_ && _[0]) {
        _ = JSON.parse(_[0]);

        return (_?.rows || [])
          .filter((row) => row?.c[0]?.f)
          .map((row) => row.c[0].f); // Extract the valid date values
      }
    } catch (error) {
      console.error('Error fetching reservations:', error);
    }

    return null;
  }

  async hydrate() {
    let reservations = await this.fetch();
    console.log('Reservations:', reservations);
    //iterate on the set
    if (reservations === null) {
      console.log('Unable to retrieve reservations');
      return;
    }
    reservations.forEach((date) => {
      const cell = document.querySelector(`[data-date="${date}"]:not(.booked)`);
      if (cell) {
        cell.textContent = 'Réservé';
        cell.classList.add('booked');
        cell.setAttribute('aria-label', 'Réservé');
      }
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

  makeReservationList(currentDate, numWeeks) {
    let _;
    const formatter = new Intl.DateTimeFormat('fr-FR', {
      day: 'numeric',
      month: 'long',
    });
    const fragment = document.createDocumentFragment();

    for (let i = 0; i < numWeeks; ++i) {
      const ISODate = this.formatDateToISO(currentDate);
      const frenchDate = `Semaine du ${formatter.format(currentDate)}`;
      const price = this.isHighSeason(ISODate) ? '1370 €' : '830 €';

      fragment.appendChild(
        Object.assign(document.createElement('li'), {
          className: 'booking-item',
          innerHTML: `
          <span class="week">${frenchDate}</span>
          <span class="price" aria-label="Prix : ${price}">${price}</span>
          <a href="mailto:info@risoul1850-vandommele.com?subject=Réservation%20pour%20la%20semaine%20du%20${ISODate}" 
             target="_blank" 
             class="book-now" 
             data-date="${ISODate}" 
             aria-label="Réserver pour la semaine du ${ISODate}">Réserver</a>
        `,
        })
      );

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

  nextSaturday(date = null) {
    let _ = date || new Date();
    _.setDate(_.getDate() + (6 - _.getDay()));
    return _;
  }
}
