const AVAILABILITY_DELAY = 400;

const normalizePersonName = (value) => {
    return value.replace(
        /(^|[\s'’])\p{L}/gu,
        (match) => match.toLocaleUpperCase()
    );
};

const normalizeUsername = (value) => {
    return value.toLocaleLowerCase();
};

const setFeedback = (
    element,
    message = '',
    type = 'error'
) => {
    if (!element) {
        return;
    }

    element.textContent = message;
    element.hidden = message === '';

    element.classList.remove(
        'text-red-600',
        'dark:text-red-400',
        'text-brand-700',
        'dark:text-accent-400'
    );

    if (message === '') {
        return;
    }

    if (type === 'success') {
        element.classList.add(
            'text-brand-700',
            'dark:text-accent-400'
        );

        return;
    }

    element.classList.add(
        'text-red-600',
        'dark:text-red-400'
    );
};

const debounce = (callback, delay) => {
    let timeout;

    return (...args) => {
        clearTimeout(timeout);

        timeout = setTimeout(
            () => callback(...args),
            delay
        );
    };
};

const initializeRegistration = () => {
    const form = document.querySelector(
        '[data-registration-form]'
    );

    if (!form) {
        return;
    }

    const availabilityUrl =
        form.dataset.availabilityUrl;

    const availabilityControllers = {
        username: null,
        email: null,
    };

    const name = form.querySelector(
        '[name="name"]'
    );

    const surname = form.querySelector(
        '[name="surname"]'
    );

    const username = form.querySelector(
        '[name="username"]'
    );

    const email = form.querySelector(
        '[name="email"]'
    );

    const password = form.querySelector(
        '[name="password"]'
    );

    const passwordConfirmation = form.querySelector(
        '[name="password_confirmation"]'
    );

    const usernameFeedback = form.querySelector(
        '[data-feedback="username"]'
    );

    const emailFeedback = form.querySelector(
        '[data-feedback="email"]'
    );

    const passwordConfirmationFeedback =
        form.querySelector(
            '[data-feedback="password_confirmation"]'
        );

    const normalizeNameField = (element) => {
        if (!element) {
            return;
        }

        element.addEventListener('input', () => {
            element.value = normalizePersonName(
                element.value
            );
        });

        element.addEventListener('blur', () => {
            element.value = element.value.trim();
        });
    };

    normalizeNameField(name);
    normalizeNameField(surname);

    const checkAvailability = async (
        field,
        value,
        feedback
    ) => {
        if (!availabilityUrl) {
            return;
        }

        availabilityControllers[field]?.abort();

        const controller = new AbortController();

        availabilityControllers[field] = controller;

        const url = new URL(
            availabilityUrl,
            window.location.origin
        );

        url.searchParams.set(
            'field',
            field
        );

        url.searchParams.set(
            'value',
            value
        );

        try {
            const response = await fetch(
                url.toString(),
                {
                    headers: {
                        Accept: 'application/json',
                    },
                    signal: controller.signal,
                }
            );

            if (!response.ok) {
                return;
            }

            const data = await response.json();

            if (controller.signal.aborted) {
                return;
            }

            if (data.available) {
                setFeedback(
                    feedback,
                    feedback.dataset.availableMessage,
                    'success'
                );

                return;
            }

            setFeedback(
                feedback,
                feedback.dataset.unavailableMessage
            );
        } catch (error) {
            if (error.name === 'AbortError') {
                return;
            }

            // Laravel will validate again on submit.
        } finally {
            if (
                availabilityControllers[field] ===
                controller
            ) {
                availabilityControllers[field] = null;
            }
        }
    };

    const cancelAvailabilityCheck = (field) => {
        availabilityControllers[field]?.abort();
        availabilityControllers[field] = null;
    };

    const checkUsernameAvailability = debounce(
        (value) => {
            checkAvailability(
                'username',
                value,
                usernameFeedback
            );
        },
        AVAILABILITY_DELAY
    );

    username?.addEventListener('input', () => {
        username.value = normalizeUsername(
            username.value
        );

        const value = username.value;

        if (value.length === 0) {
            cancelAvailabilityCheck('username');

            setFeedback(
                usernameFeedback
            );

            return;
        }

        if (value.length < 3) {
            cancelAvailabilityCheck('username');

            setFeedback(
                usernameFeedback,
                usernameFeedback.dataset.minMessage
            );

            return;
        }

        if (!/^[A-Za-z0-9_-]+$/.test(value)) {
            cancelAvailabilityCheck('username');

            setFeedback(
                usernameFeedback,
                usernameFeedback.dataset.formatMessage
            );

            return;
        }

        setFeedback(
            usernameFeedback
        );

        checkUsernameAvailability(
            value
        );
    });

    const checkEmailAvailability = debounce(
        (value) => {
            checkAvailability(
                'email',
                value,
                emailFeedback
            );
        },
        AVAILABILITY_DELAY
    );

    email?.addEventListener('input', () => {
        const value = email.value.trim();

        if (value.length === 0) {
            cancelAvailabilityCheck('email');

            setFeedback(
                emailFeedback
            );

            return;
        }

        if (!email.validity.valid) {
            cancelAvailabilityCheck('email');

            setFeedback(
                emailFeedback,
                emailFeedback.dataset.invalidMessage
            );

            return;
        }

        setFeedback(
            emailFeedback
        );

        checkEmailAvailability(
            value
        );
    });

    const validatePasswordConfirmation = () => {
        if (!passwordConfirmation?.value) {
            setFeedback(
                passwordConfirmationFeedback
            );

            return;
        }

        if (
            password?.value !==
            passwordConfirmation.value
        ) {
            setFeedback(
                passwordConfirmationFeedback,
                passwordConfirmationFeedback
                    .dataset
                    .mismatchMessage
            );

            return;
        }

        setFeedback(
            passwordConfirmationFeedback
        );
    };

    password?.addEventListener(
        'input',
        validatePasswordConfirmation
    );

    passwordConfirmation?.addEventListener(
        'input',
        validatePasswordConfirmation
    );
};

document.addEventListener(
    'DOMContentLoaded',
    initializeRegistration
);