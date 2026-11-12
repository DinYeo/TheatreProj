const { test, expect } = require('@playwright/test');

const BASE = 'http://127.0.0.1/TheatreProj/TheatreProj';

// test 1: no seat selected
test('test 1 - no seat selected should show alert', async ({ page }) => {
    await page.goto(`${BASE}/booking_form.php?perf_id=1&show_id=1`);

    const dialogPromise = new Promise(resolve => {
        page.once('dialog', async dialog => {
            resolve(dialog.message());
            await dialog.accept();
        });
    });

    await page.locator('input[type="submit"]').click();

    const msg = await dialogPromise;
    expect(msg).toContain('select at least one seat');
});

// test 2: selecting more than 8 seats
test('test 2 - selecting more than 8 seats should show alert', async ({ page }) => {
    await page.goto(`${BASE}/booking_form.php?perf_id=1&show_id=1`);

    // click the first 9 available seats (skips already booked seats)
    const availableSeats = page.locator('label.seat-shape');
    for (let i = 0; i < 9; i++) {
        await availableSeats.nth(i).click();
    }

    const dialogPromise = new Promise(resolve => {
        page.once('dialog', async dialog => {
            resolve(dialog.message());
            await dialog.accept();
        });
    });

    await page.locator('input[type="submit"]').click();

    const msg = await dialogPromise;
    expect(msg).toMatch(/8/);
});

// test 3: select 2 seats and check total price updates
test('test 3 - select 2 seats and total price updates', async ({ page }) => {
    await page.goto(`${BASE}/booking_form.php?perf_id=1&show_id=1`);

    // pick 2 available seats
    const availableSeats = page.locator('label.seat-shape');
    await availableSeats.nth(0).click();
    await availableSeats.nth(1).click();

    const total = await page.locator('#seatTotal').inputValue();
    expect(total).toBe('89.80');
});

// test 4: name with numbers should be rejected
test('test 4 - name with numbers should be rejected', async ({ page }) => {
    // 1. pick 1 available seat and submit to cart
    await page.goto(`${BASE}/booking_form.php?perf_id=1&show_id=1`);
    await page.locator('label.seat-shape').first().click();
    await page.locator('input[type="submit"]').click();

    // 2. wait for cart page to load
    await page.waitForURL(/cart\.php/);

    // 3. fill form with invalid name (contains numbers)
    await page.locator('#name').fill('john123');
    await page.locator('#email').fill('tan@gmail.com');
    await page.locator('#phone').fill('91234567');
    await page.locator('#payment').selectOption('success');

    // capture image 1: form filled with invalid name
    await page.screenshot({ path: 'test4_1_form_filled.png' });

    const dialogPromise = new Promise(resolve => {
        page.once('dialog', async dialog => {
            resolve(dialog.message());
            await dialog.accept();
        });
    });

    await page.locator('input[value="Confirm and pay"]').click();

    const msg = await dialogPromise;
    expect(msg).toMatch(/letters/i);

    // capture image 2: display the captured validation error on screen
    await page.evaluate((alertText) => {
        const banner = document.createElement('div');
        banner.innerText = 'Validation Alert: ' + alertText;
        banner.style.position = 'fixed';
        banner.style.top = '30px';
        banner.style.left = '50%';
        banner.style.transform = 'translateX(-50%)';
        banner.style.backgroundColor = '#8f1d2c';
        banner.style.color = '#ffffff';
        banner.style.padding = '14px 28px';
        banner.style.borderRadius = '6px';
        banner.style.fontSize = '16px';
        banner.style.fontWeight = 'bold';
        banner.style.boxShadow = '0 6px 16px rgba(0,0,0,0.6)';
        banner.style.zIndex = '99999';
        document.body.appendChild(banner);
    }, msg);

    await page.screenshot({ path: 'test4_2_alert_triggered.png' });
});

// test 5: invalid email format should be rejected
test('test 5 - invalid email format should be rejected', async ({ page }) => {
    // 1. pick 1 available seat and submit to cart
    await page.goto(`${BASE}/booking_form.php?perf_id=1&show_id=1`);
    await page.locator('label.seat-shape').first().click();
    await page.locator('input[type="submit"]').click();

    // 2. wait for cart page to load
    await page.waitForURL(/cart\.php/);

    // 3. fill invalid email
    await page.locator('#name').fill('John Tan');
    await page.locator('#email').fill('tan_gmail.com');
    await page.locator('#phone').fill('91234567');
    await page.locator('#payment').selectOption('success');

    // listen for js alert if fired
    let alertMsg = '';
    page.on('dialog', async dialog => {
        alertMsg = dialog.message();
        await dialog.accept();
    });

    await page.locator('input[value="Confirm and pay"]').click();

    // browser blocks invalid email via html5 validation bubble or js alert
    const validationMessage = await page.locator('#email').evaluate(el => el.validationMessage);
    const rejected = validationMessage.length > 0 || alertMsg.length > 0;
    expect(rejected).toBe(true);
});
