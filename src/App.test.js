import { render } from '@testing-library/react'
import App from './App'

test('Renders Hosting tab', () => {
  const { getByText } = render(<App />)
  const element = getByText(/^Hosting$/i)
  expect(element).toBeInTheDocument()
});
