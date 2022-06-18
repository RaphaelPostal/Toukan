module.exports = {
  purge: [],
  darkMode: false, // or 'media' or 'class'
  theme: {
    extend: {
      colors:{
        'toukan' : '#F49B22',
        'toukan-dark' : '#D68402',
        'toukan-white' : '#FFEDD0',
        'toukan-red' : '#F70303',
        'toukan-light-blue' : '#1488F3',
        'toukan-blue' : '#575A89',
        'toukan-dark-blue' : '#3F3D56',
        'toukan-green' : '#04D733',
        'toukan-dark-gray' : '#2E2E2E',
        'toukan-light-gray' : '#EDEDED',
        'toukan-salmon': '#FFDED0'
      }
    },
    fontFamily: {
      base: ['Karla', 'sans-serif'],
    },
  },
  variants: {
    extend: {},
  },
  content: ['./src/**/*.{html,js}', './node_modules/tw-elements/dist/js/**/*.js'],
  plugins: [
    require('@tailwindcss/forms'),
    require('tw-elements/dist/plugin')
  ],
}
