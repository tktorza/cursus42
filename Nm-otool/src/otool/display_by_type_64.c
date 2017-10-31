/* ************************************************************************** */
/*                                                                            */
/*                                                        :::      ::::::::   */
/*   display_by_type_64.c                               :+:      :+:    :+:   */
/*                                                    +:+ +:+         +:+     */
/*   By: tktorza <marvin@42.fr>                     +#+  +:+       +#+        */
/*                                                +#+#+#+#+#+   +#+           */
/*   Created: 2017/10/30 12:02:52 by tktorza           #+#    #+#             */
/*   Updated: 2017/10/30 12:02:53 by tktorza          ###   ########.fr       */
/*                                                                            */
/* ************************************************************************** */

#include "../../includes/nm_tool.h"

void	display_text_64(t_symtab *symt, struct section_64 *sect,
	struct mach_header_64 *header)
{
	if (((symt->bonus & BSS_OT) == 0 && (symt->bonus & DATA_OT) == 0) ||
		(symt->bonus & ALL_OT) != 0)
	{
		ft_putstr("Contents of (__TEXT,__text) section\n");
		print_res(sect->addr, sect->size, (char *)header + sect->offset);
		symt->lib == 1 ? 0 : write(1, "\n", 1);
	}
	symt->text = symt->ns;
}

void	display_data_64(t_symtab *symt, struct section_64 *sect,
	struct mach_header_64 *header)
{
	if (((symt->bonus & DATA_OT) != 0) ||
		(symt->bonus & ALL_OT) != 0)
	{
		ft_putstr("Contents of (__DATA,__data) section\n");
		print_res(sect->addr, sect->size, (char *)header + sect->offset);
		symt->lib == 1 ? 0 : write(1, "\n", 1);
	}
	symt->data = symt->ns;
}

void	display_bss_64(t_symtab *symt, struct section_64 *sect,
	struct mach_header_64 *header)
{
	if ((symt->bonus & BSS_OT) != 0 ||
		(symt->bonus & ALL_OT) != 0)
	{
		ft_putstr("Contents of (__BSS,__bss) section\n");
		print_res(sect->addr, sect->size, (char *)header + sect->offset);
		symt->lib == 1 ? 0 : write(1, "\n", 1);
	}
	symt->bss = symt->ns;
}
