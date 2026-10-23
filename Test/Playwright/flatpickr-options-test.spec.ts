import {openTestPage} from '@loki/open-test-page';
import {test, expect} from '@loki/test';
import {
    altInput,
    calendar,
    configure,
    currentMonthDay,
    FIRST_DAY_PATH,
    TEST_HANDLES,
    TEST_OPTIONS_HANDLES,
    THEMES,
    waitForFlatpickr
} from './helpers/configure';

for (const theme of THEMES) {
    test.describe(`Loki Flatpickr options (${theme})`, () => {
        test.describe.configure({mode: 'serial'});

        test.afterAll(async ({browser}) => {
            const page = await browser.newPage({baseURL: process.env.TEST_URL, ignoreHTTPSErrors: true});
            await configure(page, {config: {[FIRST_DAY_PATH]: '0'}});
            await page.close();
        });

        test('should apply global options from the XML layout', async ({page}) => {
            await configure(page, {theme});
            await openTestPage(page, TEST_OPTIONS_HANDLES);
            await waitForFlatpickr(page);

            await expect(page.locator('#flatpickr-min-date')).toHaveValue('2026-01-15');
            await expect(altInput(page, 'flatpickr-min-date')).toHaveValue('15/01/2026');

            await altInput(page, 'flatpickr-min-date').click();
            await expect(currentMonthDay(page, 'flatpickr-min-date', 15)).toHaveAttribute('aria-label', '15 \'Jan" 2026');
        });

        test('should prefer options per field over global options', async ({page}) => {
            await configure(page, {theme});
            await openTestPage(page, TEST_OPTIONS_HANDLES);
            await waitForFlatpickr(page);

            await altInput(page, 'flatpickr-field-options').click();
            await currentMonthDay(page, 'flatpickr-field-options', 15).click();
            await expect(altInput(page, 'flatpickr-field-options')).toHaveValue(/^15-\d{2}-\d{4}$/);
        });

        for (const [firstDay, weekday] of [['1', 'Mon'], ['0', 'Sun']]) {
            test(`should start the week on ${weekday} when configured`, async ({page}) => {
                await configure(page, {theme, config: {[FIRST_DAY_PATH]: firstDay}});
                await openTestPage(page, TEST_HANDLES);
                await waitForFlatpickr(page);

                await altInput(page, 'flatpickr-default').click();
                const firstWeekday = calendar(page, 'flatpickr-default').locator('.flatpickr-weekday').first();
                await expect(firstWeekday).toHaveText(weekday);
            });
        }
    });
}
