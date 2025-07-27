<style>
  .logo path,
  .logo circle {
    stroke: #000;
  }

  @media (prefers-color-scheme: dark) {
    .logo path,
    .logo circle {
      stroke: #fbbf24; /* amber-500 */
    }
  }
</style>

<svg
    class="logo"
    xmlns="http://www.w3.org/2000/svg"
    width="50"
    height="50"
    viewBox="0 0 200 200"
>
  <circle cx="100" cy="100" r="90" stroke-width="10" fill="none"/>
  <path d="M70 40 v120 h40 a50 50 0 0 0 0 -100 h-40" fill="none" stroke-width="10"/>
</svg>
