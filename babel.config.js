module.exports = {
  presets: [
    ["@babel/preset-env", {
      targets: {
        ie: 11,
        esmodules: true
      },
      useBuiltIns: "entry",
      corejs: 3,
      modules: false
    }]
  ],
  plugins: [
    "@babel/plugin-proposal-object-rest-spread",
    "@babel/plugin-proposal-class-properties",
  ],
  comments: false,
  env: {
    test: {
      plugins: [ "istanbul" ]
    }
  }
};
