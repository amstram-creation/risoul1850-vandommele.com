export default class ReservationSheet {
  constructor(selector, sheetId, sheetName) {
    this.container = document.querySelector(selector);
    this.url = `https://docs.google.com/spreadsheets/d/${sheetId}/gviz/tq?tqx=out:json&sheet=${sheetName}`;

    this.reservations = [];
    this.high_seasons = [];
    this.summer_weeks = [];
    this.prices = { high: -1, low: -1 };
  }

  async init() {
    await this.fetch();
    this.render();
    this.hydrate();
  }

render() {
  const list = this.makeReservationList(); // ← ne passe pas nextSaturday()

  this.container.innerHTML = '';
  this.container.appendChild(list);

  console.log('[DEBUG] HTML généré :');
  console.log(this.container.innerHTML);

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
      _ = _?.rows || [];

      this.reservations = _.filter((row) => row?.c[0]?.f).map(
        (row) => row.c[0].f
      );

      const ete = _.map((row) => [row.c[5]?.f, row.c[6]?.f]);
      const hiver = _.map((row) => [row.c[7]?.f, row.c[8]?.f]);

      this.summer_weeks = [...ete, ...hiver]
        .filter(([date, price]) => date && price);

      console.log('[DEBUG] Toutes les semaines (été + hiver) :');
      console.table(this.summer_weeks);


      console.log('[DEBUG] Summer weeks reçues :');
      console.table(this.summer_weeks); // ← AJOUT ICI

      this.high_seasons = _.map((row) => row.c[2]?.f).filter((date) => date);

      _ = _.map((row) => row.c[3]?.v).filter(
        (price) => price !== null && price !== undefined
      );

      this.prices.high = _[0];
      this.prices.low = _[1];

      console.log('Reservations:', this.reservations);
      console.log('High seasons:', this.high_seasons);
      console.log('Prices:', this.prices);
    }
  } catch (error) {
    console.error('Error fetching reservations:', error);
  }

  return null;
}


  hydrate() {
    if (this.reservations === null) {
      console.log('Unable to retrieve reservations');
      return;
    }

    this.reservations.forEach((date) => {
      const cell = this.container.querySelector(
        `[data-date="${date}"]:not(.booked)`
      );
      if (cell) {
        cell.textContent = 'Réservé';
        cell.classList.add('booked');
        cell.setAttribute('aria-label', 'Cette semaine est reservée');
      }
    });

    this.container
      .querySelectorAll('[data-date]:not(.booked)')
      .forEach((cell) => {
        Object.assign(cell, {
          href: `https://docs.google.com/forms/d/e/1FAIpQLSegKsOUzSgo_nkSfz9v_VsB3S5jlnlnwrQiYb-2AMTU2CqBlw/viewform?usp=pp_url&entry.494094462=${cell.dataset.date}`,

          target: '_blank',
          className: 'book-now',
          ariaLabel: `Réserver pour la semaine du ${cell.dataset.date}`,
        });
      });

  }
  makeReservationList() {
    const fragment = document.createDocumentFragment();

    this.summer_weeks.forEach(([date, price]) => {
      fragment.appendChild(this.makeReservationItem(date, price));
    });

    const ul = document.createElement('ul');
    ul.appendChild(fragment);
    return ul;
  }

  makeReservationItem(ISODate, price) {
    console.log('Creating reservation item for:', ISODate, price);
    const [year, month, day] = ISODate.split('-');
    const dateObj = new Date(year, month - 1, day);
    const formatter = new Intl.DateTimeFormat('fr-FR', {
      day: 'numeric',
      month: 'long',
      year: 'numeric',
    });

    const li = document.createElement('li');
    li.className = 'booking-item';
    li.setAttribute('data-week', ISODate);
    li.innerHTML = `
    <span class="week">Semaine du ${formatter.format(dateObj)}</span>
    <span class="price" aria-label="Prix pour la semaine">${price}</span>
    <a href="mailto:info@risoul1850-vandommele.com?subject=Réservation%20pour%20la%20semaine%20du%20${ISODate}" 
       target="_blank" 
       class="book-now" 
       data-date="${ISODate}" 
       aria-label="Réserver pour la semaine du ${ISODate}">Réserver</a>
  `;

    console.log('[DEBUG] Élément LI généré :');
    console.log(li.outerHTML); // ← Ici ✅

    return li;
  }

  priceFor(ISODate) {
    return this.high_seasons.includes(ISODate)
      ? this.prices.high
      : this.prices.low;
  }

  // isHighSeason(date) {
  //   const month = date.getMonth() + 1;
  //   const day = date.getDate();

  //   if ((month === 12 && day >= 20) || (month === 1 && day <= 7)) return true;
  //   if ((month === 2 && day >= 10) || (month === 3 && day <= 10)) return true;
  //   if (month === 4 && day <= 15) return true;
  //   if (month === 7 || month === 8) return true;
  //   if ((month === 10 && day >= 25) || (month === 11 && day <= 5)) return true;

  //   return false;
  // }

  formatDateToISO(date) {
    let _ = String(date.getFullYear());
    _ += '-' + String(date.getMonth() + 1).padStart(2, '0');
    _ += '-' + String(date.getDate()).padStart(2, '0');
    return _;
  }

  nextSaturday(date = null) {
    let _ = date || new Date();
    _.setDate(_.getDate() + (6 - _.getDay()));
    return _;
  }
}
