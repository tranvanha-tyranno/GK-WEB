async (page) => {
  const context = await page.context().browser().newContext();
  const fresh = await context.newPage();
  try {
    await fresh.route('**/api/auth/me', async route => { await new Promise(resolve => setTimeout(resolve, 800)); await route.continue(); });
    await fresh.goto('http://localhost:8080/login');
    if (!(await fresh.getByRole('button', {name:'Sign in to your workspace',exact:true}).isDisabled())) throw new Error('Submit was active before CSRF bootstrap finished');
    await fresh.getByRole('button', { name:'AN Alice',exact:true }).click();
    await fresh.getByRole('textbox', { name:'Password',exact:true }).fill('wrong');
    await fresh.getByRole('button', { name:'Sign in to your workspace',exact:true }).click();
    await fresh.getByText('Incorrect username or password.',{exact:true}).waitFor();
    await fresh.getByRole('textbox', { name:'Password',exact:true }).fill('ChatDemo!2026');
    await fresh.getByRole('button', { name:'Sign in to your workspace',exact:true }).click();
    await fresh.getByText('Connected', {exact:true}).waitFor();
    return {status:'PASS',checks:['Delayed session/CSRF bootstrap keeps form actions disabled','Demo-account action waits until ready','Wrong password shows generic error','Valid password signs in and connects']};
  } finally { await context.close(); }
}
