/**
 * Duration Picker Plugin
 *
 * @author Takuto Yanagida
 * @version 2022-02-27
 */

((wp) => {
	const el = wp.element.createElement;
	const {
		data      : { useSelect, useDispatch },
		date      : { dateI18n },
		plugins   : { registerPlugin },
		element   : { useState, useEffect, Fragment },
		components: { DateTimePicker, Button, PanelRow, Dropdown },
		editPost  : { PluginDocumentSettingPanel },
	} = wp;

	const pmk_from = window?.wpinc_duration_picker?.pmk_from    ?? '_date_from';
	const pmk_to   = window?.wpinc_duration_picker?.pmk_to      ?? '_date_to';

	const labels = window?.wpinc_duration_picker?.labels ?? {};
	const format = window?.wpinc_duration_picker?.format ?? 'Y-m-d';

	const DropdownDatePicker = ({ label, defaultLabel, currentDate, onChange, format }) => {
		const [id] = useState(() => _.uniqueId('wpinc-dropdown-date-picker__toggle-'));

		return el(
			Fragment,
			{},
			el('label', { htmlFor: id }, label),
			el(Dropdown, {
				position    : 'bottom left',
				renderToggle: ({ onToggle, isOpen }) => el(
					Fragment,
					{},
					[
						el(
							Button,
							{
								id,
								type        : 'button',
								onClick     : onToggle,
								ariaExpanded: isOpen,
								ariaLive    : 'polite',
								isLink      : true,
							},
							currentDate ? dateI18n(format, currentDate) : defaultLabel
						),
					]
				),
				renderContent: () => el(
					DateTimePicker,
					{
						currentDate,
						onChange: (v) => { onChange(v ? v.split('T')[0] : null); },
					}
				),
			})
		)
	}

	const render = () => {
		const { meta: { [pmk_from]: initDateFrom, [pmk_to]: initDateTo }} = useSelect(select => ({
			meta: select('core/editor').getEditedPostAttribute('meta') || {},
		}));

		const { editPost }            = useDispatch('core/editor');
		const [dateFrom, setDateFrom] = useState(initDateFrom);
		const [dateTo, setDateTo]     = useState(initDateTo);

		useEffect(() => {
			editPost({ meta: { [pmk_from]: (dateFrom ? dateFrom : null), [pmk_to]: (dateTo ? dateTo : null) } });
		}, [dateFrom, dateTo]);

		return el(
			PluginDocumentSettingPanel, {
				name : 'wpinc-duration-panel',
				title: labels.panel ?? 'Duration',
			},
			el(
				PanelRow,
				{},
				el(
					DropdownDatePicker,
					{
						label       : labels.date_from    ?? 'From',
						defaultLabel: labels.default_from ?? 'Pick Date',
						currentDate : dateFrom,
						onChange    : setDateFrom,
						format,
					}
				)
			),
			el(
				PanelRow,
				{},
				el(
					DropdownDatePicker,
					{
						label       : labels.date_to    ?? 'To',
						defaultLabel: labels.default_to ?? 'Pick Date',
						currentDate : dateTo,
						onChange    : setDateTo,
						format,
					}
				)
			),

		);
	};

	registerPlugin('wpinc-duration-picker', { render, icon: 'calendar-alt' });
})(window.wp);
