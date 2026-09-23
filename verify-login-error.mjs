export default async function run(page, ui) {
  await page.goto('http://localhost:8000/login')
  await page.waitForSelector('input[name=email]')

  const snap = await ui.snapshot()
  const emailRef = snap.match(/@(e\d+) textbox "Email"/)?.[1]
    ?? snap.match(/@(e\d+) textbox[^\n]*[Ee]mail/)?.[1]
  const passRef = snap.match(/@(e\d+) passwordbox/)?.[1]
    ?? snap.match(/@(e\d+) textbox "Kata sandi"/)?.[1]
  const submitRef = snap.match(/@(e\d+) button "Masuk"/)?.[1]

  if (!emailRef || !passRef || !submitRef) {
    return { error: 'refs not found', snap }
  }

  await ui.fill(emailRef, 'customer@adhijaya.test')
  await ui.fill(passRef, 'wrong-password')
  await ui.click(submitRef)
  await page.waitForSelector('[role=alert]', { timeout: 5000 })

  const alertText = await page.locator('[role=alert]').innerText()
  const bodyText = await page.locator('body').innerText()

  await page.screenshot({ path: 'shots-mobile/login-error-id.png', fullPage: true })

  return {
    alert: alertText,
    hasBanner: bodyText.includes('Masuk gagal'),
    hasIdMessage: bodyText.includes('Email atau kata sandi salah'),
    hasRawKey: bodyText.includes('auth.failed'),
  }
}
