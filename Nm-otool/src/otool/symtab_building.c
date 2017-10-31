/* ************************************************************************** */
/*                                                                            */
/*                                                        :::      ::::::::   */
/*   symtab_building.c                                  :+:      :+:    :+:   */
/*                                                    +:+ +:+         +:+     */
/*   By: tktorza <marvin@42.fr>                     +#+  +:+       +#+        */
/*                                                +#+#+#+#+#+   +#+           */
/*   Created: 2017/10/27 14:05:07 by tktorza           #+#    #+#             */
/*   Updated: 2017/10/30 12:03:08 by tktorza          ###   ########.fr       */
/*                                                                            */
/* ************************************************************************** */

#include "../../includes/nm_tool.h"

void			print_res(long unsigned int addr, unsigned int size, char *ptr)
{
	unsigned int	i;
	char			*str;

	i = 0;
	while (i < size)
	{
		if (i == 0 || i % 16 == 0)
		{
			if (i != 0)
				addr += 16;
			ft_printf("%016llx\t", addr);
		}
		str = ft_itoa_base_sub(ptr[i], 16, 2);
		ft_printf("%s ", str ? str : "00");
		free(str);
		if ((i + 1) % 16 == 0 && i + 1 < size)
			write(1, "\n", 1);
		i++;
	}
}

static void		symtab_building_bis(t_symtab *symt,
	struct segment_command_64 *seg, struct section_64 *sect,
	struct mach_header_64 *header)
{
	symt->i = 0;
	if (!verif((void *)sect))
		return (file_broken());
	while (symt->i < seg->nsects)
	{
		if (ft_strcmp(sect->sectname, SECT_TEXT) == 0 &&
				ft_strcmp(sect->segname, SEG_TEXT) == 0)
			display_text_64(symt, sect, header);
		else if (ft_strcmp(sect->sectname, SECT_DATA) == 0 &&
				ft_strcmp(sect->segname, SEG_DATA) == 0)
			display_data_64(symt, sect, header);
		else if (ft_strcmp(sect->sectname, SECT_BSS) == 0 &&
				ft_strcmp(sect->segname, SEG_DATA) == 0)
			display_bss_64(symt, sect, header);
		if (!verif((void *)sect + sizeof(sect)))
			return (file_broken());
		sect = (void *)sect + sizeof(*sect);
		symt->ns++;
		symt->i++;
	}
}

static void		symtab_building_bis_32(t_symtab *symt,
	struct segment_command *seg, struct section *sect,
	struct mach_header *header)
{
	symt->i = 0;
	if (!verif((void *)sect))
		return (file_broken());
	while (symt->i < seg->nsects)
	{
		if (ft_strcmp(sect->sectname, SECT_TEXT) == 0 &&
				ft_strcmp(sect->segname, SEG_TEXT) == 0)
			display_text_32(symt, sect, header);
		else if (ft_strcmp(sect->sectname, SECT_DATA) == 0 &&
				ft_strcmp(sect->segname, SEG_DATA) == 0)
			display_text_32(symt, sect, header);
		else if (ft_strcmp(sect->sectname, SECT_BSS) == 0 &&
				ft_strcmp(sect->segname, SEG_DATA) == 0)
			display_text_32(symt, sect, header);
		if (!verif((void *)sect + sizeof(sect)))
			return (file_broken());
		sect = (void *)sect + sizeof(*sect);
		symt->ns++;
		symt->i++;
	}
}

void			symtab_building_32(t_symtab *symt,
	struct mach_header *header, struct load_command *lc)
{
	struct segment_command	*seg;
	struct section			*sect;

	if (!verif((void *)lc))
		return (file_broken());
	while (symt->j < header->ncmds)
	{
		if (lc->cmd == LC_SEGMENT)
		{
			seg = (struct segment_command *)lc;
			sect = (struct section *)((void *)seg + sizeof(*seg));
			symtab_building_bis_32(symt, seg, sect, header);
		}
		if (!verif((void *)lc + lc->cmdsize))
			return (file_broken());
		lc = (void *)lc + lc->cmdsize;
		symt->j++;
	}
}

void			symtab_building(t_symtab *symt,
	struct mach_header_64 *header, struct load_command *lc)
{
	struct segment_command_64	*seg;
	struct section_64			*sect;

	symt->j = 0;
	if (!verif((void *)lc))
		return (file_broken());
	while (symt->j < header->ncmds)
	{
		if (lc->cmd == LC_SEGMENT_64)
		{
			seg = (struct segment_command_64 *)lc;
			sect = (struct section_64 *)((void *)seg + sizeof(*seg));
			symtab_building_bis(symt, seg, sect, header);
		}
		if (!verif((void *)lc + lc->cmdsize))
			return (file_broken());
		lc = (void *)lc + lc->cmdsize;
		symt->j++;
	}
}
