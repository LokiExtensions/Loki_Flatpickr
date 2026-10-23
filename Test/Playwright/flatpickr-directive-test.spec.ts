import {openTestPage} from '@loki/open-test-page';
import {test, expect} from '@loki/test';
import {altInput, calendar, configure, currentMonthDay, TEST_HANDLES, THEMES, waitForFlatpickr} from './helpers/configure';

for (const theme of THEMES) {
    test.describe(`Loki Flatpickr directive (${theme})`, () => {
        test.beforeEach(async ({page}) => {
            await configure(page, {theme});
            await openTestPage(page, TEST_HANDLES);
            await waitForFlatpickr(page);
        });

        test('should load the Flatpickr assets', async ({page}) => {
            await expect(page.locator('script[src*="Loki_Flatpickr/js/flatpickr.min.js"]')).toHaveCount(1);
            await expect(page.locator('link[href*="Loki_Flatpickr/css/flatpickr.min.css"][rel="stylesheet"]')).toHaveCount(1);
            expect(await page.evaluate(() => typeof (window as any).flatpickr)).toBe('function');
        });

        test('should leave RequireJS intact', async ({page}) => {
            const define = await page.evaluate(() => {
                const define = (window as any).define;
                return {
                    type: typeof define,
                    amd: !!define?.amd,
                    restored: !('lokiFlatpickrOriginalDefine' in window),
                };
            });

            expect(define.restored).toBe(true);
            if (theme === 'Magento/luma') {
                expect(define.type).toBe('function');
                expect(define.amd).toBe(true);
                await page.waitForFunction(() => new Promise((resolve) => {
                    (window as any).require(['jquery'], ($: any) => resolve(typeof $ === 'function'));
                }));
            }
        });

        test('should transform the input into a Flatpickr date picker', async ({page}) => {
            const input = page.locator('#flatpickr-default');
            await expect(input).toHaveClass(/flatpickr-input/);
            await expect(input).toHaveAttribute('type', 'hidden');
            await expect(altInput(page, 'flatpickr-default')).toBeVisible();
            await expect(altInput(page, 'flatpickr-default')).toHaveAttribute('type', 'text');
        });

        test('should select a date using the calendar', async ({page}) => {
            await altInput(page, 'flatpickr-default').click();
            await expect(calendar(page, 'flatpickr-default')).toHaveClass(/open/);

            await currentMonthDay(page, 'flatpickr-default', 15).click();

            await expect(page.locator('#flatpickr-default')).toHaveValue(/^\d{4}-\d{2}-15$/);
            await expect(altInput(page, 'flatpickr-default')).toHaveValue(/^\d{4}-\d{2}-15$/);
        });

        test('should apply options set per field', async ({page}) => {
            await altInput(page, 'flatpickr-field-options').click();
            await currentMonthDay(page, 'flatpickr-field-options', 15).click();

            await expect(page.locator('#flatpickr-field-options')).toHaveValue(/^\d{4}-\d{2}-15$/);
            await expect(altInput(page, 'flatpickr-field-options')).toHaveValue(/^15-\d{2}-\d{4}$/);
        });

        test('should disable dates before the minimum date', async ({page}) => {
            await expect(page.locator('#flatpickr-min-date')).toHaveValue('2026-01-15');
            await expect(altInput(page, 'flatpickr-min-date')).toHaveValue('2026-01-15');

            await altInput(page, 'flatpickr-min-date').click();
            await expect(currentMonthDay(page, 'flatpickr-min-date', 9)).toHaveClass(/flatpickr-disabled/);
            await expect(currentMonthDay(page, 'flatpickr-min-date', 10)).not.toHaveClass(/flatpickr-disabled/);
        });
    });
}
