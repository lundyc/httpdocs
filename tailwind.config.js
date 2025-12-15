/** @type {import('tailwindcss').Config} */
module.exports = {
  content: [
    './public/**/*.{php,html}',
    './shared/**/*.{php,html}',
    './*.php',
  ],
  theme: {
    extend: {
      colors: {
        maroon: '#000814',
        burgundy: '#001d3d',
        gold: '#ffc300',
        cream: '#f6f8ff',
        charcoal: '#003566',
        teal: '#ffd60a',
        tan: '#ffc300',
        midnight: '#000814',
      },
      fontFamily: {
        display: ['"Poppins"', '"Inter"', 'ui-sans-serif', 'system-ui'],
        sans: ['"Inter"', 'ui-sans-serif', 'system-ui'],
      },
      boxShadow: {
        'maroon-glow': '0 25px 50px -12px rgba(0, 13, 61, 0.38)',
        'gold-soft': '0 20px 30px -15px rgba(255, 195, 0, 0.45)',
      },
      backgroundImage: {
        'hero-gradient':
          'linear-gradient(135deg, rgba(0, 8, 20, 0.96), rgba(0, 53, 102, 0.9))',
        'maroon-noise':
          'linear-gradient(145deg, rgba(0,8,20,0.92), rgba(0,29,61,0.94))',
      },
      borderRadius: {
        '4xl': '2.5rem',
      },
      transitionTimingFunction: {
        'swift': 'cubic-bezier(0.22, 0.61, 0.36, 1)',
      },
    },
  },
  plugins: [require('@tailwindcss/typography'), require('@tailwindcss/line-clamp')],
};
