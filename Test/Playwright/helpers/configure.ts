import {expect, Locator, Page} from '@playwright/test';

export const THEMES = ['Magento/luma', 'Hyva/default-csp'];

export const TEST_HANDLES = ['loki_flatpickr_test'];

export const TEST_OPTIONS_HANDLES = ['loki_flatpickr_test', 'loki_flatpickr_test_options'];

export const FIRST_DAY_PATH = 'general/locale/firstday';

export const INPUT_IDS = ['flatpickr-default', 'flatpickr-field-options', 'flatpickr-min-date'];

export async function configure(page: Page, config: object) {
    const token = process.env.TEST_TOKEN;
    if (!token) {
        throw new Error('TEST_TOKEN is not set');
    }

    const response = await page.request.post(
        '/loki_functional_tests/index/configure?token=' + encodeURIComponent(token),
        {form: {config: JSON.stringify(config)}}
    );

    const body = await response.text();
    expect(response.status(), 'Configure response: ' + body).toBe(200);

    const data = JSON.parse(body);
    expect(data.error, 'Configure error').toBeUndefined();
    expect(data.result?.theme?.errors, 'Theme errors').toBeUndefined();
}

/**
 * Wait until Flatpickr is initialized on all test inputs and tag each calendar with its input ID
 */
export async function waitForFlatpickr(page: Page) {
    await page.waitForFunction((ids) => ids.every((id) => {
        const instance = (document.getElementById(id) as any)?._flatpickr;
        if (instance) {
            instance.calendarContainer.dataset.testInput = id;
        }

        return !!instance;
    }), INPUT_IDS);
}

export function altInput(page: Page, id: string): Locator {
    return page.locator(`#${id} + input`);
}

export function calendar(page: Page, id: string): Locator {
    return page.locator(`.flatpickr-calendar[data-test-input="${id}"]`);
}

export function currentMonthDay(page: Page, id: string, day: number): Locator {
    return calendar(page, id)
        .locator('.flatpickr-day:not(.prevMonthDay):not(.nextMonthDay)')
        .getByText(String(day), {exact: true});
}
